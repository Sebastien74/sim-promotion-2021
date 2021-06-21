<?php

namespace App\Entity\Api;

use App\Repository\Api\FacebookI18nRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * FacebookI18n
 *
 * @ORM\Table(name="api_facebook_i18n")
 * @ORM\Entity(repositoryClass=FacebookI18nRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookI18n
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $locale;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $domainVerification;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $phoneTrack = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Api\Facebook", inversedBy="facebookI18ns", cascade={"persist"}, fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $facebook;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDomainVerification(): ?string
    {
        return $this->domainVerification;
    }

    public function setDomainVerification(?string $domainVerification): self
    {
        $this->domainVerification = $domainVerification;

        return $this;
    }

    public function getFacebook(): ?Facebook
    {
        return $this->facebook;
    }

    public function setFacebook(?Facebook $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getPhoneTrack(): ?bool
    {
        return $this->phoneTrack;
    }

    public function setPhoneTrack(bool $phoneTrack): self
    {
        $this->phoneTrack = $phoneTrack;

        return $this;
    }
}
