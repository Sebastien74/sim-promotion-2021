<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Core\Security;
use App\Entity\Core\Website;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Form\Model\Security\Admin\RegistrationFormModel;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RegisterManager
 *
 * Manage User security registration
 *
 * @property Request $request
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property GuardAuthenticatorHandler $guardHandler
 * @property LoginFormAuthenticator $formAuthenticator
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

    /**
     * RegisterManager constructor.
     *
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $formAuthenticator
     */
    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->guardHandler = $guardHandler;
        $this->formAuthenticator = $formAuthenticator;
    }

    /**
     * Registration
     *
     * @param RegistrationFormModel $userModel
     * @param Security $security
     * @param Website $website
     * @return Response|null
     */
    public function register(RegistrationFormModel $userModel, Security $security, Website $website)
    {
        $user = $this->createUser($userModel, $website);
        $session = new Session();

        if ($security->getAdminRegistrationValidation()) {
            $session->getFlashBag()->add('success', $this->translator->trans("Merci pour inscription. Votre compte dois être validé par l'administrateur", [], 'security_cms'));
            $user->setActive(false);
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
     * @return User
     */
    private function createUser(RegistrationFormModel $userModel, Website $website)
    {
        $user = new User();

        $user->setLogin($userModel->login);
        $user->setEmail($userModel->email);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $userModel->plainPassword
            )
        );

        if (true === $userModel->agreeTerms) {
            $user->agreeTerms();
        }

        $user->addWebsite($website);
        $this->setGroup($user);

        return $user;
    }

    /**
     * Set User group
     *
     * @param User $user
     */
    private function setGroup(User $user)
    {
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->findOneBySlug('administrator');

        if (!$group) {

            $position = count($groupRepository->findAll()) + 1;

            $group = new Group();
            $group->setPosition($position);
            $group->setAdminName('Utilisateurs Front');
            $group->setSlug('administrator');

            $this->entityManager->persist($group);
            $this->entityManager->flush();
        }

        $user->setGroup($group);
    }
}