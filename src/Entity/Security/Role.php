<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Role
 *
 * @ORM\Table(name="security_role")
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity("name")
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Role extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'securityrole'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
