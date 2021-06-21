<?php

namespace App\Form\Manager\Security\Front;

use App\Entity\Core\Security;
use App\Entity\Core\Website;
use App\Entity\Security\Group;
use App\Entity\Security\Profile;
use App\Entity\Security\Role;
use App\Entity\Security\UserCategory;
use App\Entity\Security\UserFront;
use App\Form\Model\Security\Front\ProfileRegistrationModel;
use App\Form\Model\Security\Front\RegistrationFormModel;
use App\Security\LoginFrontFormAuthenticator;
use App\Service\Core\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RegisterFrontManager
 *
 * Manage UserFront security registration
 *
 * @property Request $request
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property GuardAuthenticatorHandler $guardHandler
 * @property LoginFrontFormAuthenticator $formAuthenticator
 * @property MailerService $mailer
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RegisterManager
{
    private $request;
    private $translator;
    private $entityManager;
    private $passwordEncoder;
    private $guardHandler;
    private $formAuthenticator;
    private $mailer;

    /**
     * RegisterManager constructor.
     *
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFrontFormAuthenticator $formAuthenticator
     * @param MailerService $mailer
     */
    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFrontFormAuthenticator $formAuthenticator,
        MailerService $mailer)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->guardHandler = $guardHandler;
        $this->formAuthenticator = $formAuthenticator;
        $this->mailer = $mailer;
    }

    /**
     * Registration
     *
     * @param RegistrationFormModel $userModel
     * @param Security $security
     * @param Website $website
     * @param UserCategory|null $userCategory
     * @return Response|null
     * @throws Exception
     */
    public function register(RegistrationFormModel $userModel, Security $security, Website $website, UserCategory $userCategory = NULL)
    {
        $user = $this->createUser($userModel, $website);
        $user->setCategory($userCategory);
        $session = new Session();

        if ($security->getFrontRegistrationValidation()) {
            $session->getFlashBag()->add('success', $this->translator->trans("Merci pour inscription. Votre compte dois être validé par l'administrateur.", [], 'security_cms'));
            $user->setActive(false);
        }

        if ($security->getFrontEmailConfirmation()) {

            $token = base64_encode(uniqid() . password_hash($user->getEmail(), PASSWORD_BCRYPT) . random_bytes(10));
            $token = substr(str_shuffle($token), 0, 30);

            $user->setConfirmEmail(false);
            $user->setToken($token);
            $this->sendConfirmEmail($user, $website, $token);
        }

        $session->set('user_security_post', true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $this->request,
            $this->formAuthenticator,
            'main'
        );
    }

    /**
     * Generate User
     *
     * @param RegistrationFormModel $userModel
     * @param Website $website
     * @return UserFront
     */
    private function createUser(RegistrationFormModel $userModel, Website $website)
    {
        $user = new UserFront();
        $user->setLogin($userModel->login);
        $user->setLastName($userModel->lastName);
        $user->setFirstName($userModel->firstName);
        $user->setEmail($userModel->email);
        $user->setLocale($userModel->locale);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $userModel->plainPassword
            )
        );

        if (true === $userModel->agreeTerms) {
            $user->agreeTerms();
        }

        $user->setProfile($this->setProfile($userModel->profile));
        $user->setWebsite($website);
        $this->setGroup($user);

        return $user;
    }

    private function setProfile(ProfileRegistrationModel $profileModel)
    {
        $profile = new Profile();
        $profile->setGender($profileModel->gender);

        return $profile;
    }

    /**
     * Set User group
     *
     * @param UserFront $user
     */
    private function setGroup(UserFront $user)
    {
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBySlug('front');

        if (!$group) {

            $position = count($groupRepository->findAll()) + 1;

            $group = new Group();
            $group->setPosition($position);
            $group->setAdminName('Utilisateurs Front');
            $group->setSlug('front');

            $role = $this->entityManager->getRepository(Role::class)->findOneByName('ROLE_SECURE_PAGE');
            $group->addRole($role);

            $this->entityManager->persist($group);
            $this->entityManager->flush();
        }

        $user->setGroup($group);
    }

    /**
     * Send email to UserFront email account confirmation
     *
     * @param UserFront $userFront
     * @param Website $website
     * @param string $token
     */
    private function sendConfirmEmail(UserFront $userFront, Website $website, string $token)
    {
        $frontTemplate = $website->getConfiguration()->getTemplate();

        $this->mailer->setSubject($this->translator->trans("Confirmation de votre e-mail", [], 'security_cms'));
        $this->mailer->setTo([$userFront->getEmail()]);
        $this->mailer->setTemplate('front/' . $frontTemplate . '/actions/security/email/email-confirmation.html.twig');
        $this->mailer->setArguments(['user' => $userFront, 'token' => $token]);
        $this->mailer->setWebsite($website);
        $this->mailer->send();
    }
}