<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Core\Website;
use App\Entity\Security\Role;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Form\Form;

/**
 * RoleManager
 *
 * Manage Role User in admin
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RoleManager
{
    private $entityManager;

    /**
     * RoleManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param Role $role
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function prePersist(Role $role, Website $website, array $interface, Form $form)
    {
        $this->setName($role);

        $this->entityManager->persist($role);
    }

    /**
     * @preUpdate
     *
     * @param Role $role
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(Role $role, Website $website, array $interface, Form $form)
    {
        $this->setName($role);

        $this->entityManager->persist($role);
    }

    /**
     * Set name & slug
     *
     * @param Role $role
     */
    private function setName(Role $role)
    {
        $role->setName(strtoupper($role->getName()));
        $role->setSlug(Urlizer::urlize($role->getName()));
    }
}