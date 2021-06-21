<?php

namespace App\Form\Manager\Security\Front;

use App\Entity\Core\Website;
use App\Entity\Security\UserFront;
use App\Repository\Security\UserFrontRepository;
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
 * @property UserFrontRepository $repository
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
     * @param UserFrontRepository $repository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param InformationService $informationService
     */
    public function __construct(
        MailerService $mailer,
        UserFrontRepository $repository,
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
     * @param Website $website
     * @return bool
     * @throws Exception
     */
    public function send(array $data, Website $website)
    {
        $email = $data['email'];
        $user = $this->repository->findOneBy(['email' => $email]);

        if (!$user) {
            $session = new Session();
            $session->getFlashBag()->add("error", $this->translator->trans('Aucun compte trouvé pour cet email.', [], 'security_cms'));
            return false;
        }

        $token = $this->setToken($user, $email);

        $this->sendEmail($user, $email, $token, $website);

        return true;
    }

    /**
     * Set token
     *
     * @param UserFront $user
     * @param string $email
     * @return string
     * @throws Exception
     */
    private function setToken(UserFront $user, string $email)
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
     * @param UserFront $user
     * @param string $email
     * @param string $token
     * @param Website $website
     */
    private function sendEmail(UserFront $user, string $email, string $token, Website $website)
    {
        $template = $website->getConfiguration()->getTemplate();
        $subject = $this->translator->trans('Modification de votre mot de passe.', [], 'security_cms');
        $information = $this->informationService->execute();

        $this->mailer->setSubject($subject);
        $this->mailer->setTo([$email]);
        $this->mailer->setName($information->companyName);
        $this->mailer->setFrom($information->emails->from);
        $this->mailer->setReplyTo($information->emails->noReply);
        $this->mailer->setTemplate('front/' . $template . '/actions/security/email/password-request.html.twig');
        $this->mailer->setArguments(['user' => $user, 'token' => $token]);
        $this->mailer->send();

        $session = new Session();
        $session->getFlashBag()->add("success", $this->translator->trans("Un email vous a été envoyé. Si vous ne l'avez pas reçu, pensez à vérifier dans vos spams.", [], 'security_cms'));
    }
}