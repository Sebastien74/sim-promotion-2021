<?php

namespace App\Entity\Seo;

use App\Repository\Seo\SessionCityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SessionCity
 *
 * @ORM\Table(name="seo_session_city")
 * @ORM\Entity(repositoryClass=SessionCityRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionCity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Seo\SessionGroup", mappedBy="city", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $groups;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seo\SessionCountry", inversedBy="cities", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * SessionCity constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection|SessionGroup[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(SessionGroup $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->setCity($this);
        }

        return $this;
    }

    public function removeGroup(SessionGroup $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            // set the owning side to null (unless already changed)
            if ($group->getCity() === $this) {
                $group->setCity(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?SessionCountry
    {
        return $this->country;
    }

    public function setCountry(?SessionCountry $country): self
    {
        $this->country = $country;

        return $this;
    }
}
