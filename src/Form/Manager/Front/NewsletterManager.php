<?php

namespace App\Form\Manager\Front;

use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Module\Newsletter\Email;
use App\Service\Content\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NewsletterManager
 *
 * Manage front Newsletter Action
 *
 * @property RecaptchaService $recaptcha
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewsletterManager
{
    private $recaptcha;
    private $entityManager;
    private $request;
    private $translator;

    /**
     * NewsletterManager constructor.
     *
     * @param RecaptchaService $recaptcha
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RecaptchaService $recaptcha,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        TranslatorInterface $translator)
    {
        $this->recaptcha = $recaptcha;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
        $this->translator = $translator;
    }

    /**
     * Execute request
     *
     * @param FormInterface $form
     * @param Campaign $campaign
     * @param Email $email
     * @return bool
     * @throws Exception
     */
    public function execute(FormInterface $form, Campaign $campaign, Email $email)
    {
        if (!$this->recaptcha->execute($campaign->getWebsite(), $campaign, $form, $email->getEmail())) {
            return false;
        }

        if ($campaign->getInternalRegistration()) {
            $this->addEmail($campaign, $email);
        }

        $this->sendEmailConfirmation($campaign, $email);

        $session = new Session();
        $session->getFlashBag()->add("success", $this->translator->trans('Merci pour votre inscription !!', [], 'front'));

        return true;
    }

    /**
     * Add Email to Campaign
     *
     * @param Campaign $campaign
     * @param Email $email
     */
    private function addEmail(Campaign $campaign, Email $email)
    {
        $email->setLocale($this->request->getLocale());

        $campaign->addEmail($email);

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();
    }

    /**
     * Send email confirmation
     *
     * @param Campaign $campaign
     * @param Email $email
     */
    private function sendEmailConfirmation(Campaign $campaign, Email $email)
    {
        if ($campaign->getEmailConfirmation()) {
            die;
        }
    }
}