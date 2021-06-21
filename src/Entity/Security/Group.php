<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Repository\Security\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group
 *
 * @ORM\Table(name="security_group")
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Group extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'securitygroup'
    ];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $loginRedirection;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Security\Role", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="security_groups_roles",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    private $roles;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getLoginRedirection(): ?string
    {
        return $this->loginRedirection;
    }

    public function setLoginRedirection(?string $loginRedirection): self
    {
        $this->loginRedirection = $loginRedirection;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }
}
