<?php

namespace App\Entity\Api;

use App\Repository\Api\FacebookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Facebook
 *
 * @ORM\Table(name="api_facebook")
 * @ORM\Entity(repositoryClass=FacebookRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Facebook
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
    private $apiVersion = 'v3.1';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pageId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $appId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiSecretKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiPublicKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiGraphVersion;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Api", inversedBy="facebook", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $api;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Api\FacebookI18n", mappedBy="facebook", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @ORM\OrderBy({"locale"="ASC"})
	 * @Assert\Valid()
	 */
	private $facebookI18ns;

    public function __construct()
    {
        $this->facebookI18ns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageId(): ?string
    {
        return $this->pageId;
    }

    public function setPageId(?string $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getApiSecretKey(): ?string
    {
        return $this->apiSecretKey;
    }

    public function setApiSecretKey(?string $apiSecretKey): self
    {
        $this->apiSecretKey = $apiSecretKey;

        return $this;
    }

    public function getApiPublicKey(): ?string
    {
        return $this->apiPublicKey;
    }

    public function setApiPublicKey(?string $apiPublicKey): self
    {
        $this->apiPublicKey = $apiPublicKey;

        return $this;
    }

    public function getApiGraphVersion(): ?string
    {
        return $this->apiGraphVersion;
    }

    public function setApiGraphVersion(?string $apiGraphVersion): self
    {
        $this->apiGraphVersion = $apiGraphVersion;

        return $this;
    }

    public function getApi(): ?Api
    {
        return $this->api;
    }

    public function setApi(Api $api): self
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @return Collection|FacebookI18n[]
     */
    public function getFacebookI18ns(): Collection
    {
        return $this->facebookI18ns;
    }

    public function addFacebookI18n(FacebookI18n $facebookI18n): self
    {
        if (!$this->facebookI18ns->contains($facebookI18n)) {
            $this->facebookI18ns[] = $facebookI18n;
            $facebookI18n->setFacebook($this);
        }

        return $this;
    }

    public function removeFacebookI18n(FacebookI18n $facebookI18n): self
    {
        if ($this->facebookI18ns->removeElement($facebookI18n)) {
            // set the owning side to null (unless already changed)
            if ($facebookI18n->getFacebook() === $this) {
                $facebookI18n->setFacebook(null);
            }
        }

        return $this;
    }
}
