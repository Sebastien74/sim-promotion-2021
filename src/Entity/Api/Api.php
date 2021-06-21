<?php

namespace App\Entity\Api;

use App\Entity\Core\Website;
use App\Repository\Api\ApiRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Api
 *
 * @ORM\Table(name="api")
 * @ORM\Entity(repositoryClass=ApiRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Api
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $addThis;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $shareLinks = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $shareLinkFixed = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayShareLinks = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayShareNames = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tawkToId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securitySecretKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securitySecretIv;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Facebook", mappedBy="api", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $facebook;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Google", mappedBy="api", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $google;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Instagram", mappedBy="api", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $instagram;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Custom", mappedBy="api", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $custom;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Website", inversedBy="api", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\PrePersist
     * @throws Exception
     */
    public function prePersist()
    {
        $securitySecretIv = base64_encode(uniqid() . password_hash(uniqid(), PASSWORD_BCRYPT) . random_bytes(10));
        $this->securitySecretKey = substr(str_shuffle($securitySecretIv), 0, 45);
        $securitySecretKey = base64_encode(uniqid() . password_hash(uniqid(), PASSWORD_BCRYPT) . random_bytes(10));
        $this->securitySecretKey = substr(str_shuffle($securitySecretKey), 0, 45);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddThis(): ?string
    {
        return $this->addThis;
    }

    public function setAddThis(?string $addThis): self
    {
        $this->addThis = $addThis;

        return $this;
    }

    public function getShareLinks(): ?array
    {
        return $this->shareLinks;
    }

    public function setShareLinks(?array $shareLinks): self
    {
        $this->shareLinks = $shareLinks;

        return $this;
    }

    public function getShareLinkFixed(): ?bool
    {
        return $this->shareLinkFixed;
    }

    public function setShareLinkFixed(bool $shareLinkFixed): self
    {
        $this->shareLinkFixed = $shareLinkFixed;

        return $this;
    }

    public function getDisplayShareLinks(): ?bool
    {
        return $this->displayShareLinks;
    }

    public function setDisplayShareLinks(bool $displayShareLinks): self
    {
        $this->displayShareLinks = $displayShareLinks;

        return $this;
    }

    public function getDisplayShareNames(): ?bool
    {
        return $this->displayShareNames;
    }

    public function setDisplayShareNames(bool $displayShareNames): self
    {
        $this->displayShareNames = $displayShareNames;

        return $this;
    }

    public function getTawkToId(): ?string
    {
        return $this->tawkToId;
    }

    public function setTawkToId(string $tawkToId): self
    {
        $this->tawkToId = $tawkToId;

        return $this;
    }

    public function getSecuritySecretKey(): ?string
    {
        return $this->securitySecretKey;
    }

    public function setSecuritySecretKey(?string $securitySecretKey): self
    {
        $this->securitySecretKey = $securitySecretKey;

        return $this;
    }

    public function getSecuritySecretIv(): ?string
    {
        return $this->securitySecretIv;
    }

    public function setSecuritySecretIv(?string $securitySecretIv): self
    {
        $this->securitySecretIv = $securitySecretIv;

        return $this;
    }

    public function getFacebook(): ?Facebook
    {
        return $this->facebook;
    }

    public function setFacebook(Facebook $facebook): self
    {
        $this->facebook = $facebook;

        // set the owning side of the relation if necessary
        if ($this !== $facebook->getApi()) {
            $facebook->setApi($this);
        }

        return $this;
    }

    public function getGoogle(): ?Google
    {
        return $this->google;
    }

    public function setGoogle(Google $google): self
    {
        $this->google = $google;

        // set the owning side of the relation if necessary
        if ($this !== $google->getApi()) {
            $google->setApi($this);
        }

        return $this;
    }

    public function getInstagram(): ?Instagram
    {
        return $this->instagram;
    }

    public function setInstagram(Instagram $instagram): self
    {
        $this->instagram = $instagram;

        // set the owning side of the relation if necessary
        if ($this !== $instagram->getApi()) {
            $instagram->setApi($this);
        }

        return $this;
    }

    public function getCustom(): ?Custom
    {
        return $this->custom;
    }

    public function setCustom(?Custom $custom): self
    {
        $this->custom = $custom;

        // set (or unset) the owning side of the relation if necessary
        $newApi = null === $custom ? null : $this;
        if ($custom->getApi() !== $newApi) {
            $custom->setApi($newApi);
        }

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(Website $website): self
    {
        $this->website = $website;

        return $this;
    }
}