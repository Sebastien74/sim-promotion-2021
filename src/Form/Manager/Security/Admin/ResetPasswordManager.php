<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use App\Service\Content\InformationService;
use App\Service\Core\MailerService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * ResetPasswordManager
 *
 * Manage User security reset password
 *
 * @property MailerService $mailer
 * @property UserRepository $repository
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property InformationService $informationService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ResetPasswordManager
{
    private $mailer;
    private $repository;
    private $translator;
    private $entityManager;
    private $informationService;

    /**
     * ResetPasswordManager constructor.
     *
     * @param MailerService $mailer
     * @param UserRepository $repository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param InformationService $informationService
     */
    public function __construct(
        MailerService $mailer,
        UserRepository $repository,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        InformationService $informationService)
    {
        $this->mailer = $mailer;
        $this->repository = $repository;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->informationService = $informationService;
    }

    /**
     * Send request
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function send(array $data)
    {
        $email = $data['email'];
        $user = $this->repository->findOneBy(['email' => $email]);

        if (!$user) {
            $session = new Session();
            $session->getFlashBag()->add("error", $this->translator->trans('Aucun compte trouvé pour cet email.', [], 'security_cms'));
            return false;
        }

        $token = $this->setToken($user, $email);

        $this->sendEmail($user, $email, $token);

        return true;
    }

    /**
     * Set token
     *
     * @param User $user
     * @param string $email
     * @return string
     * @throws Exception
     */
    private function setToken(User $user, string $email)
    {
        $token = base64_encode(uniqid() . password_hash($email, PASSWORD_BCRYPT) . random_bytes(10));
        $token = substr(str_shuffle($token), 0, 30);
        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));

        $user->setToken($token);
        $user->setTokenRequest($now);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * Send email
     *
     * @param User $user
     * @param string $email
     * @param string $token
     */
    private function sendEmail(User $user, string $email, string $token)
    {
        $subject = $this->translator->trans('Modification de votre mot de passe.', [], 'security_cms');
        $information = $this->informationService->execute();

        $this->mailer->setSubject($subject);
        $this->mailer->setTo([$email]);
        $this->mailer->setName($information->companyName);
        $this->mailer->setFrom($information->emails->from);
        $this->mailer->setReplyTo($information->emails->noReply);
        $this->mailer->setTemplate('security/email/password-request.html.twig');
        $this->mailer->setArguments(['user' => $user, 'token' => $token]);
        $this->mailer->send();

        $session = new Session();
        $session->getFlashBag()->add("success", $this->translator->trans("Un email vous a été envoyé. Si vous ne l'avez pas reçu, pensez à vérifier dans vos spams.", [], 'security_cms'));
    }
}