<?php

namespace App\Form\Manager\Security\Front;

use App\Entity\Core\Website;
use App\Entity\Security\Group;
use App\Entity\Security\Role;
use App\Entity\Security\UserFront;
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
     * @param UserFront $user
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(UserFront $user, Website $website)
    {
        $user->setAgreeTerms(true);
        $user->setAgreesTermsAt(new DateTime('now'));
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getPlainPassword()
            )
        );

        $this->setGroup($user);

        $this->entityManager->persist($user);
    }

    /**
     * @preUpdate
     *
     * @param UserFront $user
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(UserFront $user, Website $website, array $interface, Form $form)
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $user->getPlainPassword()
                )
            );
        } else {
            $this->pictureManager->execute($user, $form);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
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
}