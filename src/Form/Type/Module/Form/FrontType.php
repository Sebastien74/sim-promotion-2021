<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\ContactValue;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Form\StepForm;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\FieldConfiguration;
use App\Entity\Layout\Layout;
use App\Entity\Translation\i18n;
use App\Form\Validator as Validator;
use App\Form\Widget as WidgetType;
use App\Helper\Core\InterfaceHelper;
use App\Twig\Translation\IntlRuntime;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontType
 *
 * @property Request $request
 * @property string $locale
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property IntlRuntime $intlExtension
 * @property InterfaceHelper $interfaceHelper
 * @property bool $setGroup
 * @property bool $disablePicker
 * @property array $options
 * @property array $constraints
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontType extends AbstractType
{
    private $request;
    private $locale;
    private $entityManager;
    private $translator;
    private $intlExtension;
    private $interfaceHelper;
    private $setGroup = false;
    private $disablePicker = false;
    private $options = [];
    private $constraints = [];

    /**
     * FrontType constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param IntlRuntime $intlExtension
     * @param InterfaceHelper $interfaceHelper
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        IntlRuntime $intlExtension,
        InterfaceHelper $interfaceHelper)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->intlExtension = $intlExtension;
        $this->interfaceHelper = $interfaceHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['form_data'];

        $recaptcha = new WidgetType\RecaptchaType($this->translator);
        $recaptcha->add($builder, $entity->getConfiguration());

        if ($entity instanceof Form) {
            $this->setForm($entity, $builder);
        } elseif ($entity instanceof StepForm) {
            $this->setStepForm($entity, $builder);
        }
    }

    /**
     * Generate Form
     *
     * @param Form $form
     * @param FormBuilderInterface $builder
     * @throws Exception
     */
    private function setForm(Form $form, FormBuilderInterface $builder)
    {
        $this->addLayoutFields($form->getLayout(), $builder);
    }

    /**
     * Generate StepForm
     *
     * @param StepForm $stepForm
     * @param FormBuilderInterface $builder
     * @throws Exception
     */
    private function setStepForm(StepForm $stepForm, FormBuilderInterface $builder)
    {
        foreach ($stepForm->getForms() as $form) {
            $this->addLayoutFields($form->getLayout(), $builder);
        }
    }

    /**
     * Generate Layout fields
     *
     * @param Layout $layout
     * @param FormBuilderInterface $builder
     * @throws Exception
     */
    private function addLayoutFields(Layout $layout, FormBuilderInterface $builder)
    {
        foreach ($layout->getZones() as $zone) {
            foreach ($zone->getCols() as $col) {
                foreach ($col->getBlocks() as $block) {
                    $fieldType = $block->getBlockType()->getFieldType();
                    if (!empty($fieldType)) {
                        $this->setField($fieldType, 'field_' . $block->getId(), $block, $builder);
                    }
                }
            }
        }
    }

    /**
     * Generate field
     *
     * @param string $fieldType
     * @param string $fieldName
     * @param Block $block
     * @param FormBuilderInterface $builder
     * @param ContactValue|null $value
     * @param bool $setGroup
     * @param bool $disablePicker
     * @throws Exception
     */
    public function setField(string $fieldType, string $fieldName, Block $block, FormBuilderInterface $builder, ContactValue $value = NULL, bool $setGroup = false, bool $disablePicker = false)
    {
        $asText = [Type\DateType::class, Type\DateTimeType::class];
        $fieldType = $value && in_array($fieldType, $asText) ? Type\TextType::class : $fieldType;

        $this->setGroup = $setGroup;
        $this->disablePicker = $disablePicker;
        $this->options = [];
        $this->constraints = [];

        $this->getOptions($fieldType, $block, $value);
        $builder->add($fieldName, $fieldType, $this->options);
    }

    /**
     * Get options
     *
     * @param string $fieldType
     * @param Block $block
     * @param ContactValue|null $value
     * @throws Exception
     */
    private function getOptions(string $fieldType, Block $block, ContactValue $value = NULL)
    {
        $configuration = $block->getFieldConfiguration();
        $i18n = $this->getI18n($block);
        $blockType = $block->getBlockType()->getSlug();

        $this->setRequired($fieldType, $configuration, $i18n);
        $this->setLabel($fieldType, $i18n);
        $this->setIcon($block);
        $this->setClasses($fieldType, $configuration);
        $this->setValue($fieldType, $i18n, $value);
        $this->setPlaceholder($fieldType, $i18n, $configuration);
        $this->setHelp($i18n);
        $this->setConstraints($fieldType, $configuration);
        $this->setPicker($fieldType, $configuration);
        $this->setRegEx($fieldType, $blockType, $configuration, $i18n);
        $this->setChoices($fieldType, $configuration);
        $this->setEntity($fieldType, $configuration);
    }

    /**
     * Set required field
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     * @param i18n $i18n
     */
    private function setRequired(string $fieldType, FieldConfiguration $configuration, i18n $i18n)
    {
        $excludes = [Type\SubmitType::class];

        if (!in_array($fieldType, $excludes)) {

            $options = [];
            $isRequired = $configuration->getRequired();
            $regex = $configuration->getRegex();
            $i18nMessage = $i18n && $i18n->getError() ? $i18n->getError() : NULL;
            $checkBoxMessage = $i18nMessage && $fieldType === Type\CheckboxType::class ? $i18nMessage : $this->translator->trans('Veuillez accepter', [], 'front_form');
            $message = $checkBoxMessage && $fieldType === Type\CheckboxType::class ? $checkBoxMessage : $i18nMessage;
            $this->options['required'] = $isRequired;

            if (!$regex && $isRequired && $message) {
                $options['message'] = $message;
            }

            if ($isRequired) {
                $this->options['constraints'][] = new Assert\NotBlank($options);
            }
        }
    }

    /**
     * Get label
     *
     * @param string $fieldType
     * @param i18n $i18n
     */
    private function setLabel(string $fieldType, i18n $i18n)
    {
        if ($fieldType === Type\HiddenType::class) {
            $this->options['label'] = false;
        } else {
            $this->options['label'] = $i18n && $i18n->getTitle() ? $i18n->getTitle() : false;
            if ($fieldType === Type\SubmitType::class && !$this->options['label']) {
                $this->options['label'] = $this->translator->trans('Envoyer', [], 'front_form');
            }
        }
    }

    /**
     * Get icon
     *
     * @param Block $block
     */
    private function setIcon(Block $block)
    {
        if ($block->getIcon()) {
            $this->options['attr']['data-icon'] = $block->getIcon();
        }
    }

    /**
     * Set classes
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     */
    private function setClasses(string $fieldType, FieldConfiguration $configuration)
    {
        $class = !empty($this->options['attr']['class']) ? $this->options['attr']['class'] : '';

        if ($fieldType === Type\SubmitType::class) {
            $color = $configuration->getBlock()->getColor();
            $buttonColor = $color ? $color : 'btn-primary';
            $this->options['attr']['class'] = $class . ' btn ' . $buttonColor . ' hbtn hb-fill-right';
        } elseif ($fieldType === Type\ChoiceType::class
            && $configuration->getMultiple()
            && $configuration->getPicker()
            && !$configuration->getExpanded()) {
            $this->options['attr']['class'] = $class . ' select-choice';
        } elseif ($fieldType === Type\CheckboxType::class
            || $fieldType === Type\ChoiceType::class) {
            $this->options['display'] = 'form-check';
            $this->options['label_class'] = 'form-check-label';
        }

        if ($configuration->getInline()) {
            $this->options['attr']['class'] = $class . ' form-check-inline mr-0';
        }

        $matches = explode('\\', $fieldType);
        $type = str_replace('Type', '', end($matches));

        if ($this->setGroup) {
            $block = $configuration->getBlock();
            $blockSize = $block->getSize();
            $colSize = $block->getCol()->getSize();
            $size = $colSize < $blockSize ? $colSize : $blockSize;
            $this->options['attr']['group'] = strtolower($type) . '-group col-md-' . $size;
        } else {
            $this->options['attr']['group'] = strtolower($type) . '-group';
        }
    }

    /**
     * Get value
     *
     * @param string $fieldType
     * @param i18n $i18n
     * @param ContactValue|null $value
     * @throws Exception
     */
    private function setValue(string $fieldType, i18n $i18n, ContactValue $value = NULL)
    {
        $data = NULL;
        if ($fieldType === Type\HiddenType::class) {
            $data = $i18n && $i18n->getTitle() ? $i18n->getTitle() : NULL;
        } elseif ($fieldType === Type\DateType::class && $value instanceof ContactValue) {
            $data = $value->getValue() ? new DateTime($value->getValue()) : NULL;
        } elseif ($fieldType === Type\CheckboxType::class && $value instanceof ContactValue) {
            $data = boolval($value->getValue());
        }

        if ($data) {
            $this->options['data'] = $data;
        }
    }

    /**
     * Get placeholder
     *
     * @param string $fieldType
     * @param i18n $i18n
     * @param FieldConfiguration $configuration
     * @return bool
     * @throws Exception
     */
    private function setPlaceholder(string $fieldType, i18n $i18n, FieldConfiguration $configuration)
    {
        $exceptionFields = [];
        $excludesFields = [Type\SubmitType::class];
        $optionsFields = [Type\ChoiceType::class, EntityType::class];
        $datesFields = [Type\DateType::class, Type\DateTimeType::class, Type\TimeType::class];
        $exceptionFields = array_merge($exceptionFields, $datesFields);
        $isOptions = in_array($fieldType, $optionsFields);
        $placeholder = $i18n && $i18n->getPlaceholder() ? $i18n->getPlaceholder() : ($isOptions ? $this->translator->trans('Sélectionnez', [], 'front_form') : NULL);

        if (in_array($fieldType, $excludesFields) || !$placeholder && !in_array($fieldType, $exceptionFields)) {
            return false;
        }

        if ($isOptions) {
            $this->options['placeholder'] = $placeholder;
            $this->options['attr']['data-placeholder'] = $placeholder;
        } elseif (!$configuration->getPicker() && in_array($fieldType, $datesFields)) {
            $this->getDatesOptions($configuration);
        } else {
            $this->options['attr']['placeholder'] = $placeholder;
            $this->options['attr']['data-placeholder'] = $placeholder;
        }
    }

    /**
     * Get field Date options
     *
     * @param FieldConfiguration $configuration
     * @throws Exception
     */
    private function getDatesOptions(FieldConfiguration $configuration)
    {
        /** Placeholder */
        $this->options['placeholder'] = [
            'year' => $this->translator->trans('Année', [], 'front_form'),
            'month' => $this->translator->trans('Mois', [], 'front_form'),
            'day' => $this->translator->trans('Jour', [], 'front_form'),
            'hour' => $this->translator->trans('Heure', [], 'front_form'),
            'minute' => $this->translator->trans('Minute', [], 'front_form'),
            'second' => $this->translator->trans('Seconde', [], 'front_form'),
        ];

        /** Year options */
        $minData = $configuration->getMin();
        $maxData = $configuration->getMax();
        if ($maxData && !$minData || $minData > $maxData && !empty($maxData)) {
            return;
        }

        $startDatetime = $minData > 0 ? new DateTime(strval($minData . '-01-01')) : new DateTime('now');
        $referDatetimeStart = $minData > 0 ? new DateTime(strval($minData . '-01-01')) : new DateTime('now');
        $start = intval($startDatetime->format('Y'));
        $endDatetime = $maxData > 0 ? new DateTime(strval($maxData . '-01-01')) : $referDatetimeStart->add(new DateInterval('P100Y'));
        $end = intval($endDatetime->format('Y'));

        $years = [];
        for ($y = $start; $y <= $end; $y++) {
            $years[] = $y;
        }

        $this->options['years'] = $years;
    }

    /**
     * Get helper
     *
     * @param i18n $i18n
     */
    private function setHelp(i18n $i18n)
    {
        $help = $i18n->getHelp();

        if ($help) {
            $this->options['help'] = $help;
        }
    }

    /**
     * Get placeholder
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     */
    private function setConstraints(string $fieldType, FieldConfiguration $configuration)
    {
        $excludes = [Type\SubmitType::class];
        if (!in_array($fieldType, $excludes)) {
            if ($fieldType === Type\EmailType::class) {
                $this->options['constraints'][] = new Assert\Email();
            } elseif ($fieldType === Type\UrlType::class) {
                $this->options['constraints'][] = new Assert\Url();
            }
        }

        $excludes = [Type\DateType::class, Type\DateTimeType::class, Type\TimeType::class, Type\FileType::class];
        if (!in_array($fieldType, $excludes)) {
            if ($configuration->getMin() || $configuration->getMax()) {
                if (is_numeric($configuration->getMin())) {
                    $this->options['attr']['minlength'] = intval($configuration->getMin());
                }
                if (is_numeric($configuration->getMax())) {
                    $this->options['attr']['maxlength'] = intval($configuration->getMax());
                }
                $this->options['constraints'][] = new Assert\Length([
                    'min' => is_numeric($configuration->getMin()) ? intval($configuration->getMin()) : false,
                    'max' => is_numeric($configuration->getMax()) ? intval($configuration->getMax()) : false,
                ]);
            }
        }

        if ($fieldType === Type\FileType::class) {

            $fileTypes = $configuration->getFilesTypes();

            $this->options['multiple'] = $configuration->getMultiple();
            $this->options['help'] = $this->getFileHelp($fileTypes);
            if ($this->options['multiple']) {
                $this->options['data_class'] = NULL;
            }
            if ($configuration->getBlock()->getColor()) {
                $this->options['attr']['data-btn'] = $configuration->getBlock()->getColor();
            }

            /** Constraints */
            $constraints = [];
            if ($configuration->getMaxFileSize()) {
                $constraints['maxSize'] = $configuration->getMaxFileSize() . 'k';
            }
            $mimeTypes = $this->getMimeTypes($fileTypes);
            if ($fileTypes && !empty($mimeTypes['mimeTypes']) && !empty($mimeTypes['accept'])) {
                $this->options['attr']['accept'] = $mimeTypes['accept'];
                if (!$this->options['multiple']) {
                    $constraints['mimeTypes'] = $mimeTypes['mimeTypes'];
                }
            }
            if ($constraints) {
                $this->options['constraints'][] = new Assert\File($constraints);
            }

        } elseif ($fieldType === Type\TelType::class) {
            $this->options['constraints'][] = new Validator\Phone();
        }
    }

    /**
     * Set field as picker
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     * @throws Exception
     */
    private function setPicker(string $fieldType, FieldConfiguration $configuration)
    {
        if ($configuration->getPicker()) {

            $class = !empty($this->options['attr']['class']) ? $this->options['attr']['class'] : '';
            $fieldsDate = [Type\DateType::class, Type\DateTimeType::class, Type\TimeType::class];

            if (in_array($fieldType, $fieldsDate) && !$this->disablePicker) {
                $this->options['widget'] = 'single_text';
                $this->options['html5'] = false;
                $this->options['attr']['class'] = $class . ' datepicker';
                $this->options['attr']['data-type'] = $fieldType === Type\DateType::class ? 'date' : ($fieldType === Type\DateTimeType::class ? 'datetime' : 'hour');
                if ($fieldType === Type\DateType::class) {
                    $this->options['format'] = $this->intlExtension->formatDate($this->locale)->dateLarge;
                }
            } elseif ($fieldType === Type\CountryType::class || Type\LanguageType::class || EntityType::class) {
                $this->options['attr']['class'] = $class . ' select-choice';
            }
        }
    }

    /**
     * Set regEx
     *
     * @param string $fieldType
     * @param string $blockType
     * @param FieldConfiguration $configuration
     * @param i18n $i18n
     */
    private function setRegEx(string $fieldType, string $blockType, FieldConfiguration $configuration, i18n $i18n)
    {
        $regex = $configuration->getRegex();
        $firstValid = substr($regex, 0, 1) === '/';
        $lastValid = substr($regex, -1) === '/';

        if ($regex && $firstValid && $lastValid) {
            $message = $i18n->getError() ? $i18n->getError() : $this->translator->trans('This value is not valid.', [], 'validators');
            $this->options['constraints'][] = new Assert\Regex([
                'message' => $message,
                'pattern' => $regex
            ]);
        } elseif ($blockType === 'form-zip-code') {
            $this->options['constraints'][] = new Validator\ZipCode();
        }
    }

    /**
     * Set Choices
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     */
    private function setChoices(string $fieldType, FieldConfiguration $configuration)
    {
        if ($fieldType === Type\ChoiceType::class) {

            $class = !empty($this->options['attr']['class']) ? $this->options['attr']['class'] : '';

            $this->options['multiple'] = $configuration->getMultiple();
            $this->options['expanded'] = $configuration->getExpanded() && !$configuration->getPicker();
            $this->options['choices'] = [];

            if (!$this->options['expanded'] && $configuration->getPicker()) {
                $this->options['attr']['class'] = $class . ' select-choice';
            }

            foreach ($configuration->getFieldValues() as $value) {
                foreach ($value->getI18ns() as $i18n) {
                    if ($i18n->getLocale() === $this->locale) {
                        $this->options['choices'][$i18n->getIntroduction()] = $i18n->getBody();
                    }
                }
            }
        }
    }

    /**
     * Set Entity Type options
     *
     * @param string $fieldType
     * @param FieldConfiguration $configuration
     */
    private function setEntity(string $fieldType, FieldConfiguration $configuration)
    {
        if ($fieldType === EntityType::class) {

            $this->options['class'] = $configuration->getClassName();
            $this->options['query_builder'] = function (EntityRepository $er) {
                $className = $er->getClassName();
                $entities = $this->entityManager->getRepository($className)->findAll();
                $referEntity = $entities ? $entities[0] : NULL;
                if ($referEntity) {
                    $website = $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
                    $interface = $this->interfaceHelper->generate($className);
                    $masterGetter = !empty($interface['masterField']) ? 'get' . ucfirst($interface['masterField']) : NULL;
                    $masterWebsite = $masterGetter && method_exists($referEntity, $masterGetter) && method_exists($referEntity->$masterGetter(), 'getWebsite');
                    if ($masterGetter && $masterWebsite) {
                        return $er->createQueryBuilder('e')
                            ->leftJoin('e.' . $interface['masterField'], 'j')
                            ->andWhere('j.website = :website')
                            ->setParameter('website', $website)
                            ->addSelect('j')
                            ->orderBy('e.position', 'ASC');
                    } elseif (method_exists($referEntity, 'getWebsite')) {
                        return $er->createQueryBuilder('e')
                            ->andWhere('e.website = :website')
                            ->setParameter('website', $website)
                            ->orderBy('e.position', 'ASC');
                    } else {
                        return $er->createQueryBuilder('e')
                            ->orderBy('e.position', 'ASC');
                    }
                }
            };
            $this->options['choice_label'] = function ($entity) {
                return strip_tags($entity->getAdminName());
            };
        }
    }

    /**
     * Get i18n
     *
     * @param Block $block
     * @return i18n|bool|mixed
     */
    private function getI18n(Block $block)
    {
        foreach ($block->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $this->locale) {
                return $i18n;
            }
        }

        return false;
    }

    /**
     * Get File help message
     *
     * @param array $fileTypes
     * @return null|string
     */
    private function getFileHelp(array $fileTypes = [])
    {
        $help = '';
        foreach ($fileTypes as $fileType) {
            $help .= $fileType . ', ';
        }
        $help = rtrim($help, ', ');

        $message = count($fileTypes) > 1
            ? $this->translator->trans('Types de fichiers acceptés :', [], 'front_form')
            : $this->translator->trans('Type de fichier accepté :', [], 'front_form');

        return $help ? $message . ' ' . $help : NULL;
    }

    /**
     * Get mime types for constraints
     *
     * @param array $fileTypes
     * @return array
     */
    private function getMimeTypes(array $fileTypes): array
    {
        $allMimeTypes = [
            '.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            '.xls' => 'application/vnd.ms-excel',
            'image/*' => 'image/*',
            '.doc' => 'application/msword',
            '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            '.txt' => 'text/plain',
            '.pdf' => 'application/pdf',
            '.mp4' => 'video/mp4',
            '.mp3' => 'audio/mpeg',
        ];

        $accept = '';
        $mimeTypes = [];
        foreach ($fileTypes as $fileType) {
            $accept .= $fileType . ', ';
            if (!empty($allMimeTypes[$fileType])) {
                $mimeTypes[] = $allMimeTypes[$fileType];
            }
        }

        return [
            'accept' => rtrim($accept, ', '),
            'mimeTypes' => $mimeTypes
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'form_data' => NULL,
            'translation_domain' => 'front_form'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "front_form";
    }
}