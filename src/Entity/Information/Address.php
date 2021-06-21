<?php

namespace App\Entity\Information;

use App\Entity\BaseEntity;
use App\Repository\Information\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address
 *
 * @ORM\Table(name="information_address")
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Address extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'address'
    ];

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max = 20)
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $googleMapUrl;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $googleMapDirectionUrl;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $zones = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $schedule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\Phone", mappedBy="address", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\Email", mappedBy="address", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $emails;

    /**
     * Address constructor.
     */
    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->emails = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getGoogleMapUrl(): ?string
    {
        return $this->googleMapUrl;
    }

    public function setGoogleMapUrl(?string $googleMapUrl): self
    {
        $this->googleMapUrl = $googleMapUrl;

        return $this;
    }

    public function getGoogleMapDirectionUrl(): ?string
    {
        return $this->googleMapDirectionUrl;
    }

    public function setGoogleMapDirectionUrl(?string $googleMapDirectionUrl): self
    {
        $this->googleMapDirectionUrl = $googleMapDirectionUrl;

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

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setAddress($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
            // set the owning side to null (unless already changed)
            if ($phone->getAddress() === $this) {
                $phone->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setAddress($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
            // set the owning side to null (unless already changed)
            if ($email->getAddress() === $this) {
                $email->setAddress(null);
            }
        }

        return $this;
    }
}
