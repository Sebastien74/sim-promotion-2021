<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Entity\Information\Address;
use App\Repository\Security\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="security_profile")
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Profile extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'profile'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $gender;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\User", mappedBy="profile", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\UserFront", mappedBy="profile", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $userFront;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Information\Address", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="security_profile_addresses",
     *      joinColumns={@ORM\JoinColumn(name="profile_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $addresses;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserFront(): ?UserFront
    {
        return $this->userFront;
    }

    public function setUserFront(?UserFront $userFront): self
    {
        $this->userFront = $userFront;

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }
}
