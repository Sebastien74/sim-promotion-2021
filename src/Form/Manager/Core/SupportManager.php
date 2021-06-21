<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Website;
use App\Service\Core\MailerService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SupportManager
 *
 * To send email at support in admin
 *
 * @property MailerService $mailer
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SupportManager
{
    private $mailer;
    private $translator;

    /**
     * SupportManager constructor.
     *
     * @param MailerService $mailer
     * @param TranslatorInterface $translator
     */
    public function __construct(MailerService $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * Send request
     *
     * @param array $data
     * @param Website $website
     */
    public function send(array $data, Website $website)
    {
        $configuration = $website->getConfiguration();
        $emails = array_merge($configuration->getEmailsDev(), $configuration->getEmailsSupport());

        $this->mailer->setSubject($this->translator->trans('Agence Félix Support', [], 'security_cms'));
        $this->mailer->setName($data['name']);
        $this->mailer->setTo(array_unique($emails));
        $this->mailer->setReplyTo($data['email']);
        $this->mailer->setBaseTemplate('base-felix');
        $this->mailer->setTemplate('admin/page/core/support-email.html.twig');
        $this->mailer->setArguments([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'message' => $data['message']
        ]);
        $this->mailer->send();

        $session = new Session();
        $session->getFlashBag()->add("success", $this->translator->trans("Votre message a été envoyé avec succès. Nous y répondrons dès que possible", [], 'admin'));
    }
}