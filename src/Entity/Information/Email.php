<?php

namespace App\Entity\Information;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Information\EmailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Email
 *
 * @ORM\Table(name="information_email")
 * @ORM\Entity(repositoryClass=EmailRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Email extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'email'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entitled;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $zones = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $deletable = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Information\Address", inversedBy="emails", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Information\Information", inversedBy="emails", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $information;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="information_email_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="email_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * Email constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getEntitled(): ?string
    {
        return $this->entitled;
    }

    public function setEntitled(?string $entitled): self
    {
        $this->entitled = $entitled;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getZones(): ?array
    {
        return $this->zones;
    }

    public function setZones(?array $zones): self
    {
        $this->zones = $zones;

        return $this;
    }

    public function getDeletable(): ?bool
    {
        return $this->deletable;
    }

    public function setDeletable(bool $deletable): self
    {
        $this->deletable = $deletable;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getInformation(): ?Information
    {
        return $this->information;
    }

    public function setInformation(?Information $information): self
    {
        $this->information = $information;

        return $this;
    }

    /**
     * @return Collection|i18n[]
     */
    public function getI18ns(): Collection
    {
        return $this->i18ns;
    }

    public function addI18n(i18n $i18n): self
    {
        if (!$this->i18ns->contains($i18n)) {
            $this->i18ns[] = $i18n;
        }

        return $this;
    }

    public function removeI18n(i18n $i18n): self
    {
        if ($this->i18ns->contains($i18n)) {
            $this->i18ns->removeElement($i18n);
        }

        return $this;
    }
}
