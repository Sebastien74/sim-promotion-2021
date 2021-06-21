<?php

namespace App\Entity\Api;

use App\Repository\Api\GoogleI18nRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * GoogleI18n
 *
 * @ORM\Table(name="api_google_i18n")
 * @ORM\Entity(repositoryClass=GoogleI18nRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GoogleI18n
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
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analyticsUa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analyticsAccountId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analyticsStatsDuration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tagManagerKey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tagManagerLayer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $searchConsoleKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mapKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $placeId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Api\Google", inversedBy="googleI18ns", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $google;

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

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getAnalyticsUa(): ?string
    {
        return $this->analyticsUa;
    }

    public function setAnalyticsUa(?string $analyticsUa): self
    {
        $this->analyticsUa = $analyticsUa;

        return $this;
    }

    public function getAnalyticsAccountId(): ?string
    {
        return $this->analyticsAccountId;
    }

    public function setAnalyticsAccountId(?string $analyticsAccountId): self
    {
        $this->analyticsAccountId = $analyticsAccountId;

        return $this;
    }

    public function getAnalyticsStatsDuration(): ?string
    {
        return $this->analyticsStatsDuration;
    }

    public function setAnalyticsStatsDuration(?string $analyticsStatsDuration): self
    {
        $this->analyticsStatsDuration = $analyticsStatsDuration;

        return $this;
    }

    public function getTagManagerKey(): ?string
    {
        return $this->tagManagerKey;
    }

    public function setTagManagerKey(?string $tagManagerKey): self
    {
        $this->tagManagerKey = $tagManagerKey;

        return $this;
    }

    public function getTagManagerLayer(): ?string
    {
        return $this->tagManagerLayer;
    }

    public function setTagManagerLayer(?string $tagManagerLayer): self
    {
        $this->tagManagerLayer = $tagManagerLayer;

        return $this;
    }

    public function getSearchConsoleKey(): ?string
    {
        return $this->searchConsoleKey;
    }

    public function setSearchConsoleKey(?string $searchConsoleKey): self
    {
        $this->searchConsoleKey = $searchConsoleKey;

        return $this;
    }

    public function getMapKey(): ?string
    {
        return $this->mapKey;
    }

    public function setMapKey(?string $mapKey): self
    {
        $this->mapKey = $mapKey;

        return $this;
    }

    public function getPlaceId(): ?string
    {
        return $this->placeId;
    }

    public function setPlaceId(?string $placeId): self
    {
        $this->placeId = $placeId;

        return $this;
    }

    public function getGoogle(): ?Google
    {
        return $this->google;
    }

    public function setGoogle(?Google $google): self
    {
        $this->google = $google;

        return $this;
    }
}
