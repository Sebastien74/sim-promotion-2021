<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Form\Manager\Security\PictureManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * UserManager
 *
 * Manage User in admin
 *
 * @property EntityManagerInterface $entityManager
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property PictureManager $pictureManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserManager
{
    private $entityManager;
    private $passwordEncoder;
    private $pictureManager;

    /**
     * UserManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param PictureManager $pictureManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, PictureManager $pictureManager)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->pictureManager = $pictureManager;
    }

    /**
     * @prePersist
     *
     * @param User $user
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(User $user, Website $website)
    {
        $user->setAgreeTerms(true);
        $user->setAgreesTermsAt(new DateTime('now'));
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getPlainPassword()
            )
        );

        $this->entityManager->persist($user);
    }

    /**
     * @preUpdate
     *
     * @param User $user
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(User $user, Website $website, array $interface, Form $form)
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $user->getPlainPassword()
                )
            );
            $user->setResetPasswordDate(new DateTime('now'));
            $user->setResetPassword(false);
        } else {
            $this->pictureManager->execute($user, $form);
        }

        $this->entityManager->persist($user);
    }
}