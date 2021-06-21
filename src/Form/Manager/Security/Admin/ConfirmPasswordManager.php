<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Security\User;
use App\Form\Model\Security\Admin\PasswordResetModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * ConfirmPasswordManager
 *
 * Manage User security password
 *
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfirmPasswordManager
{
    private $passwordEncoder;
    private $entityManager;

    /**
     * ConfirmPasswordManager constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * Set user password
     *
     * @param PasswordResetModel $passwordResetModel
     * @param User $user
     * @throws Exception
     */
    public function confirm(PasswordResetModel $passwordResetModel, User $user)
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $passwordResetModel->plainPassword
            )
        );

        $slugsAlert = ['password-info', 'password-alert'];
        $alerts = $user->getAlerts();
        foreach ($slugsAlert as $key => $slug) {
            if (in_array($slug, $alerts)) {
                unset($alerts[$key]);
            }
        }

        $user->setResetPasswordDate(new DateTime('now'));
        $user->setToken(NULL);
        $user->setAlerts($alerts);
        $user->setResetPassword(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}