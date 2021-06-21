<?php

namespace App\Service\Development;

use App\Entity\Security\Group;
use App\Entity\Security\Role;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * SecurityService
 *
 * @property EntityManagerInterface $entityManager
 * @property User $webmaster
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityService
{
    private $entityManager;
    private $webmaster;
    private $io;

    /**
     * SecurityService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->webmaster = $this->entityManager->getRepository(User::class)->findOneBy(['login' => 'webmaster']);
    }

    /**
     * To create Role
     *
     * @param string $roleName
     * @param string $roleEntitled
     * @param SymfonyStyle|null $io
     * @return Role
     */
    public function addRole(string $roleName, string $roleEntitled, SymfonyStyle $io = NULL): Role
    {
        $this->io = $io;

        $repository = $this->entityManager->getRepository(Role::class);
        $role = $repository->findOneBy(['name' => $roleName]);
        $position = count($repository->findAll(['name' => $roleName])) + 1;

        if (!$role) {

            $role = new Role();
            $role->setAdminName($roleEntitled);
            $role->setName($roleName);
            $role->setSlug(Urlizer::urlize($roleName));
            $role->setPosition($position);
            $role->setCreatedBy($this->webmaster);

            $this->entityManager->persist($role);
            $this->entityManager->flush();

            $this->ioMessage('[OK] Role ' . $roleName . ' successfully generated.');
        } else {
            $this->ioMessage('[WARNING] Role ' . $roleName . ' already exists.');
        }

        if ($this->io) {
            $this->io->newLine();
        }

        return $role;
    }

    /**
     * To add Role in Group
     *
     * @param Role $role
     * @param string $slugGroup
     * @param SymfonyStyle|null $io
     */
    public function addRoleInGroup(Role $role, string $slugGroup, SymfonyStyle $io = NULL): void
    {
        $this->io = $io;

        $group = $this->entityManager->getRepository(Group::class)->findOneBy(['slug' => $slugGroup]);

        if (!$this->roleInGroup($role, $group)) {
            $group->addRole($role);
            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->ioMessage('[OK] Role ' . $role->getName() . ' was successfully added to ' . $group->getAdminName() . ' group.');
        } else {
            $this->ioMessage('[WARNING] Role ' . $role->getName() . ' has already been assigned to ' . $group->getAdminName() . ' group.');
        }

        if ($this->io) {
            $this->io->newLine();
        }
    }

    /**
     * Check if Role already exist in Group
     *
     * @param Role $role
     * @param Group $group
     * @return bool
     */
    private function roleInGroup(Role $role, Group $group): bool
    {
        foreach ($group->getRoles() as $groupRole) {
            if ($groupRole->getName() === $role->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * IO output command message
     *
     * @param string $message
     */
    private function ioMessage(string $message): void
    {
        if ($this->io) {
            $this->io->write($message);
        }
    }
}