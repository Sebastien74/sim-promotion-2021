<?php

namespace App\Form\Manager\Front;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactStepForm;
use App\Entity\Module\Form\ContactValue;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Form\StepForm;
use App\Entity\Layout\FieldConfiguration;
use App\Entity\Seo\FormSuccess;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Translation\i18n;
use App\Service\Content\RecaptchaService;
use App\Service\Core\MailerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Button;
use Symfony\Component\Form as Component;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormManager
 *
 * Manage front Form Action
 *
 * @property RecaptchaService $recaptcha
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property MailerService $mailer
 * @property KernelInterface $kernel
 * @property Session $session
 * @property array $fields
 * @property string $sender
 * @property array $receivers
 * @property bool $senderInForm
 * @property string $phone
 * @property array $configurations
 * @property array $attachments
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormManager
{
    private $recaptcha;
    private $translator;
    private $entityManager;
    private $request;
    private $mailer;
    private $kernel;
    private $session;
    private $fields;
    private $sender;
    private $receivers = [];
    private $senderInForm = true;
    private $phone;
    private $configurations = [];
    private $attachments = [];

    /**
     * FormManager constructor.
     *
     * @param RecaptchaService $recaptcha
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param MailerService $mailer
     * @param KernelInterface $kernel
     */
    public function __construct(
        RecaptchaService $recaptcha,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        MailerService $mailer,
        KernelInterface $kernel)
    {
        $this->recaptcha = $recaptcha;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->mailer = $mailer;
        $this->kernel = $kernel;
        $this->session = new Session();
    }

    /**
     * Set errors flashBags
     *
     * @param FormInterface $form
     */
    public function errors(FormInterface $form)
    {
        foreach ($form->all() as $child) {
            if (!$child instanceof SubmitButton && !$child instanceof Button) {
                $data = $child->getData();
                if ($data instanceof UploadedFile) {
                    $this->session->getFlashBag()->add($child->getName() . '_message_uploaded_file', $this->translator->trans("Veuillez recharger votre fichier", [], 'front_form'));
                } elseif (is_object($data) && method_exists($data, 'getId')) {
                    $this->session->getFlashBag()->add($child->getName() . '_value', $data->getId());
                } else {
                    $this->session->getFlashBag()->add($child->getName() . '_value', $data);
                }
                if (!$child->isValid()) {
                    foreach ($child->getErrors() as $error) {
                        $this->session->getFlashBag()->add($child->getName() . '_message', $error->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Get Contact by request Token
     *
     * @return ContactForm|ContactStepForm|null
     */
    public function getContact()
    {
        $contact = NULL;
        $token = !empty($_GET['token']) ? $_GET['token'] : NULL;

        if ($token) {

            $contact = $this->entityManager->getRepository(ContactForm::class)->findOneBy(['token' => $token, 'tokenExpired' => false]);

            if (!$contact) {
                $contact = $this->entityManager->getRepository(ContactStepForm::class)->findOneBy(['token' => $token, 'tokenExpired' => false]);
            }

            if (!$contact) {
                header("Status: 301 Moved Permanently", false, 301);
                header('Location:' . $this->request->getSchemeAndHttpHost());
                exit();
            }
        }

        $form = $contact instanceof ContactForm ? $contact->getForm() : ($contact instanceof ContactStepForm ? $contact->getStepform() : NULL);
        $removeToken = $form instanceof Form ? $form->getCalendars()->isEmpty() : true;

        if ($contact && $removeToken) {
            $contact->setTokenExpired(true);
            $this->entityManager->persist($contact);
            $this->entityManager->flush();
        }

        return $contact;
    }

    /**
     * Process if form is valid
     *
     * @param Form|StepForm $form
     * @param FormInterface $formPost
     * @return bool|ContactForm|ContactStepForm
     * @throws Exception
     */
    public function success($form, FormInterface $formPost)
    {
        $configuration = $form->getConfiguration();
        $website = $form->getWebsite();
        $data = $formPost->getData();
        $haveCalendars = $configuration->getCalendarsActive() && $form->getCalendars()->count() > 0;

        $this->formSuccess($website, $form);

        if ($form instanceof Form) {
            $this->getFields($form, $data);
        } elseif ($form instanceof StepForm) {
            foreach ($form->getForms() as $stepForm) {
                $this->getFields($stepForm, $data);
            }
        }

        $this->getConfigurations();

        if (!$this->sender) {
            $formSender = $form->getConfiguration()->getSendingEmail();
            $this->sender = $formSender ? $formSender : 'dev@felix-creation.fr';
            $this->senderInForm = false;
        }

        if (!$this->recaptcha->execute($website, $configuration, $formPost, $this->sender)) {
            return false;
        }

        $i18n = $this->getI18n($form);

        if (!$this->checkContact($form)) {
            return false;
        }

        $contact = $this->addContact($form);
        $this->setAttachments($form, $contact);

        if (!$haveCalendars) {

            $this->sendEmail($website, $form, $i18n);

            if ($configuration->getConfirmEmail() && $i18n->confirmation) {
                $this->sendConfirm($website, $form, $i18n);
            }

            if (!$configuration->getThanksModal()) {
                $this->session->getFlashBag()->add('success_form', $i18n->alert);
            }
        }

        return $contact;
    }

    /**
     * Form success tracking
     *
     * @param Website $website
     * @param Form|StepForm $form
     */
    private function formSuccess(Website $website, $form)
    {
        $successForm = new FormSuccess();
        $successForm->setWebsite($website);
        $successForm->setUrl($this->request->headers->get('referer'));

        if ($form instanceof Form) {
            $successForm->setForm($form);
        } elseif ($form instanceof StepForm) {
            $successForm->setStepForm($form);
        }

        $this->entityManager->persist($successForm);
        $this->entityManager->flush();
    }

    /**
     * Get all fields data
     *
     * @param Form $form
     * @param array $data
     */
    private function getFields(Form $form, array $data): void
    {
        $excludes = ['form-submit', 'form-password'];

        foreach ($form->getLayout()->getZones() as $zone) {
            foreach ($zone->getCols() as $col) {
                foreach ($col->getBlocks() as $block) {

                    $blockTypeSlug = $block->getBlockType()->getSlug();

                    foreach ($block->getI18ns() as $i18n) {

                        if ($i18n->getLocale() === $this->request->getLocale() && !in_array($blockTypeSlug, $excludes)) {

                            $fieldData = $this->getFieldData($block, $i18n, $data);

                            $this->fields['field_' . $block->getId()] = [
                                'label' => $fieldData->label,
                                'value' => $fieldData->value
                            ];

                            if ($blockTypeSlug === "form-emails") {
                                $this->receivers[] = $this->fields['field_' . $block->getId()]['value'];
                            }

                            if ($blockTypeSlug === "form-email") {
                                $this->sender = $this->fields['field_' . $block->getId()]['value'];
                            }

                            if ($blockTypeSlug === "form-phone") {
                                $this->phone = $this->fields['field_' . $block->getId()]['value'];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get form field sConfigurations
     */
    private function getConfigurations(): void
    {
        $blockRepository = $this->entityManager->getRepository(Block::class);

        foreach ($this->fields as $keyName => $field) {
            $matches = explode('_', $keyName);
            $this->configurations[$keyName] = $blockRepository->find(end($matches))->getFieldConfiguration();
        }
    }

    /**
     * Get field data
     *
     * @param Block $block
     * @param i18n $i18n
     * @param array $data
     * @return object
     */
    private function getFieldData(Block $block, i18n $i18n, array $data)
    {
        $label = strlen(strip_tags($i18n->getTitle())) > 0 ? $i18n->getTitle() : $i18n->getPlaceholder();
        $value = !empty($data['field_' . $block->getId()]) ? $data['field_' . $block->getId()] : NULL;

        if (is_array($value) && count($value) === 1 && !$label) {
            $fieldValues = $block->getFieldConfiguration()->getFieldValues();
            foreach ($fieldValues as $fieldValue) {
                foreach ($fieldValue->getI18ns() as $i18n) {
                    if ($i18n->getLocale() === $this->request->getLocale()) {
                        $label = $i18n->getIntroduction();
                        break;
                    }
                }
            }
        }

        return (object)[
            'label' => $label,
            'value' => $value
        ];
    }

    /**
     * Get messages to alert & email confirmation
     *
     * @param mixed $entity
     * @return object
     */
    public function getI18n($entity)
    {
        $alert = $this->translator->trans("Merci pour votre message !!", [], 'front_form');
        $subject = $entity->getAdminName();

        foreach ($entity->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $this->request->getLocale()) {
                $alert = !empty(strip_tags($i18n->getPlaceholder())) ? $i18n->getPlaceholder() : $alert;
                $title = !empty(strip_tags($i18n->getTitle())) ? $i18n->getTitle() : NULL;
                $subject = !empty(strip_tags($i18n->getTitle())) ? $i18n->getTitle() : $subject;
                $confirmation = !empty(strip_tags($i18n->getBody())) ? $i18n->getBody() : NULL;
                $confirmationSubject = !empty(strip_tags($i18n->getTitle())) ? $i18n->getTitle() : NULL;
                break;
            }
        }

        return (object)[
            'alert' => $alert,
            'title' => !empty($title) ? $title : NULL,
            'subject' => !empty($subject) ? $subject : $entity->getAdminName(),
            'confirmation' => !empty($confirmation) ? $confirmation : NULL,
            'confirmationSubject' => !empty($confirmationSubject) ? $confirmationSubject : $subject,
        ];
    }

    /**
     * If Form as unique contact check if email already existing
     *
     * @param Form|StepForm $form
     * @return bool
     */
    private function checkContact($form): bool
    {
        if ($form->getConfiguration()->getUniqueContact() && ($this->senderInForm || $this->phone)) {

            if ($this->senderInForm) {
                $existing = $this->entityManager->getRepository(ContactForm::class)->findOneBy([
                    'form' => $form,
                    'email' => $this->sender
                ]);
            } else {
                $existing = $this->entityManager->getRepository(ContactForm::class)->findOneBy([
                    'form' => $form,
                    'phone' => $this->phone
                ]);
            }

            if ($existing) {
                $message = $this->senderInForm
                    ? $this->translator->trans('Cet email existe déjà', [], 'front_form')
                    : $this->translator->trans('Ce téléphone existe déjà', [], 'front_form');
                $this->session->getFlashBag()->add('error_form', $message);
                return false;
            }
        }

        return true;
    }

    /**
     * Add Contact to DB
     *
     * @param Form|StepForm $form
     * @return ContactForm|ContactStepForm
     * @throws Exception
     */
    private function addContact($form)
    {
        $registrationValid = $this->checkRegistration($form);

        if ($form->getConfiguration()->getDbRegistration() && $registrationValid) {

            $contact = NULL;
            if ($form instanceof Form) {
                $contact = new ContactForm();
            } elseif ($form instanceof StepForm) {
                $contact = new ContactStepForm();
            }

            $token = bin2hex(random_bytes(64));
            $contact->setToken($token);

            $requestCalendar = !empty($_GET['calendar']) ? $_GET['calendar'] : NULL;
            if ($requestCalendar) {
                $contact->setCalendar($this->entityManager->getRepository(Calendar::class)->find($requestCalendar));
            }

            $email = $this->senderInForm ? $this->sender : NULL;
            $contact->setEmail($email);
            $contact->setPhone($this->phone);
            $form->addContact($contact);

            foreach ($this->fields as $keyName => $field) {

                $setField = false;
                $value = new ContactValue();
                $fieldValue = is_array($field['value']) ? json_encode($field['value']) : $field['value'];
                $fieldConfiguration = !empty($this->configurations[$keyName]) ? $this->configurations[$keyName] : NULL;
                $fieldType = $fieldConfiguration instanceof FieldConfiguration ? $fieldConfiguration->getBlock()->getBlockType()->getFieldType() : NULL;

                if ($fieldValue instanceof DateTime) {
                    $setField = true;
                    $fieldValue = $fieldType === Component\Extension\Core\Type\DateType::class ? $fieldValue->format('Y-m-d') : $fieldValue->format('Y-m-d H:m:i');
                    $this->fields[$keyName]['value'] = $fieldValue;
                } elseif ($fieldType === Component\Extension\Core\Type\CheckboxType::class || is_bool($fieldValue)) {
                    $setField = true;
                    $fieldValue = $fieldValue ? 'true' : 'false';
                } elseif (!is_object($fieldValue) && !empty($this->configurations[$keyName])) {
                    $setField = true;
                } elseif ($fieldType === EntityType::class) {
                    $setField = true;
                    $fieldValue = $fieldValue->getId();
                    if ($fieldConfiguration->getClassName() === Calendar::class) {
                        $contact->setCalendar($this->entityManager->getRepository(Calendar::class)->find($fieldValue));
                    }
                } elseif (is_object($fieldValue) && method_exists($fieldValue, 'getAdminAdminName')) {
                    $setField = true;
                    $fieldValue = $fieldValue->getAdminName();
                }

                if ($setField) {
                    $value->setLabel($field['label']);
                    $value->setValue($fieldValue);
                    $value->setConfiguration($this->configurations[$keyName]);
                    $contact->addContactValue($value);
                }
            }

            $this->entityManager->persist($form);
            $this->entityManager->flush();

            return $contact;
        }
    }

    /**
     * Check if registration is not close
     *
     * @param Form|StepForm $form
     * @return bool
     * @throws Exception
     */
    private function checkRegistration($form)
    {
        $configuration = $form->getConfiguration();
        $maxShipments = $configuration->getMaxShipments();

        if ($configuration->getPublicationEnd() && new DateTime('now') > $configuration->getPublicationEnd()) {
            return false;
        }

        if ($maxShipments && $form->getContacts()->count() >= $maxShipments) {
            return false;
        }

        return true;
    }

    /**
     * Set attachments
     *
     * @param Form|StepForm $form
     * @param ContactForm|ContactStepForm|null $contact
     */
    private function setAttachments($form, $contact = NULL)
    {
        $flushContact = false;

        foreach ($this->fields as $keyName => $field) {

            $isFilesArray = is_array($field['value']) && !empty($field['value'][0]) && $field['value'][0] instanceof UploadedFile;
            $fieldValue = is_array($field['value']) ? json_encode($field['value']) : $field['value'];

            if ($fieldValue instanceof UploadedFile) {
                $flushContact = $this->uploadedFile($field, $keyName, $form, $fieldValue, $contact);
            } elseif ($isFilesArray) {
                foreach ($field['value'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $flushContact = $this->uploadedFile($field, $keyName, $form, $file, $contact);
                    }
                }
                unset($this->fields[$keyName]);
            }
        }

        if ($flushContact) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush();
        }
    }

    /**
     * To upload File
     *
     * @param array $field
     * @param string $keyName
     * @param Form|StepForm $form
     * @param UploadedFile $fieldValue
     * @param ContactForm|ContactStepForm|null $contact
     * @return bool
     */
    private function uploadedFile(array $field, string $keyName, $form, UploadedFile $fieldValue, $contact = NULL)
    {
        $flushContact = false;

        /** @var UploadedFile $fieldValue */

        $formType = $form instanceof Form ? 'forms' : 'steps-forms';
        $publicDirname = '/public/uploads/emails/' . $formType . '/' . $form->getId() . '/contacts/' . $contact->getId();
        $fileDirname = $this->kernel->getProjectDir() . $publicDirname;
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
        $extension = $fieldValue->getClientOriginalExtension();
        $filename = Urlizer::urlize(str_replace('.' . $extension, '', $fieldValue->getClientOriginalName())) . '.' . $extension;
        $fieldValue->move($fileDirname, $filename);

        $this->attachments[] = $fileDirname . '/' . $filename;

        if ($contact) {

            $value = new ContactValue();
            $value->setLabel($field['label']);
            $value->setValue(str_replace('/public', '', $publicDirname) . '/' . $filename);
            $value->setConfiguration($this->configurations[$keyName]);

            $contact->addContactValue($value);

            $flushContact = true;
        }

        return $flushContact;
    }

    /**
     * Send email
     *
     * @param Website $website
     * @param Form|StepForm $form
     * @param mixed $i18n
     */
    private function sendEmail(Website $website, $form, $i18n)
    {
        $filesystem = new Filesystem();
        $frontTemplate = $website->getConfiguration()->getTemplate();
        $formReceivers = array_merge($this->receivers, $form->getConfiguration()->getReceivingEmails());
        $templateEmailDirname = $this->kernel->getProjectDir() . '/templates/front/' . $frontTemplate . '/actions/form/email/' . $form->getSlug() . '.html.twig';
        $templateEmailDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templateEmailDirname);
        $templateEmail = $filesystem->exists($templateEmailDirname)
            ? 'front/' . $frontTemplate . '/actions/form/email/' . $form->getSlug() . '.html.twig'
            : 'front/' . $frontTemplate . '/actions/form/email/default.html.twig';

        $receivers = [];
        foreach ($formReceivers as $receiver) {
            $receivers = array_merge($receivers, explode(',', $receiver));
        }

        $this->mailer->setSubject($i18n->subject);
        $this->mailer->setTo($receivers);
        $this->mailer->setName($this->getCompanyName($website));
        $this->mailer->setFrom($form->getConfiguration()->getSendingEmail());
        $this->mailer->setSender($form->getConfiguration()->getSendingEmail());
        $this->mailer->setReplyTo($this->sender);
        $this->mailer->setTemplate($templateEmail);
        $this->mailer->setArguments(['fields' => $this->fields]);
        if ($form->getConfiguration()->getAttachmentsInMail()) {
            $this->mailer->setAttachments($this->attachments);
        }
        $this->mailer->send();
    }

    /**
     * To send email confirmation
     *
     * @param Website $website
     * @param Form|StepForm $form
     * @param mixed $i18n
     */
    private function sendConfirm(Website $website, $form, $i18n)
    {
        $filesystem = new Filesystem();
        $frontTemplate = $website->getConfiguration()->getTemplate();
        $templateEmailDirname = $this->kernel->getProjectDir() . '/templates/front/' . $frontTemplate . '/actions/form/email/' . $form->getSlug() . '-confirmation.html.twig';
        $templateEmail = $filesystem->exists($templateEmailDirname)
            ? 'front/' . $frontTemplate . '/actions/form/email/' . $form->getSlug() . '-confirmation.html.twig'
            : 'front/' . $frontTemplate . '/actions/form/email/default-confirmation.html.twig';

        $this->mailer->setSubject($i18n->confirmationSubject);
        $this->mailer->setTo([$this->sender]);
        $this->mailer->setName($this->getCompanyName($website));
        $this->mailer->setFrom($form->getConfiguration()->getSendingEmail());
        $this->mailer->setSender($form->getConfiguration()->getSendingEmail());
        $this->mailer->setReplyTo($form->getConfiguration()->getSendingEmail());
        $this->mailer->setTemplate($templateEmail);
        $this->mailer->setArguments(['message' => $i18n->confirmation]);
        $this->mailer->send();
    }

    /**
     * Get company name
     *
     * @param Website $website
     * @return string|null
     */
    public function getCompanyName(Website $website)
    {
        $companyName = NULL;

        foreach ($website->getInformation()->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $this->request->getLocale()) {
                $companyName = $i18n->getTitle();
                break;
            }
        }

        return $companyName;
    }
}