<?php

namespace App\Entity\Information;

use App\Entity\BaseEntity;
use App\Repository\Information\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Phone
 *
 * @ORM\Table(name="information_phone")
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Phone extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'phone'
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tagNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $zones = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Information\Address", inversedBy="phones", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Information\Information", inversedBy="phones", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $information;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTagNumber(): ?string
    {
        return $this->tagNumber;
    }

    public function setTagNumber(?string $tagNumber): self
    {
        $this->tagNumber = $tagNumber;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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
}
