<?php

namespace App\Entity\Seo;

use App\Repository\Seo\SessionGroupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * SessionGroup
 *
 * @ORM\Table(name="seo_session_group")
 * @ORM\Entity(repositoryClass=SessionGroupRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $anonymize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $localeVisit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seo\SessionCity", inversedBy="groups", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnonymize(): ?string
    {
        return $this->anonymize;
    }

    public function setAnonymize(string $anonymize): self
    {
        $this->anonymize = $anonymize;

        return $this;
    }

    public function getLocaleVisit(): ?string
    {
        return $this->localeVisit;
    }

    public function setLocaleVisit(?string $localeVisit): self
    {
        $this->localeVisit = $localeVisit;

        return $this;
    }

    public function getCity(): ?SessionCity
    {
        return $this->city;
    }

    public function setCity(?SessionCity $city): self
    {
        $this->city = $city;

        return $this;
    }
}
