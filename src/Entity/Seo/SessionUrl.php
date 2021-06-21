<?php

namespace App\Entity\Seo;

use App\Repository\Seo\SessionUrlRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * SessionUrl
 *
 * @ORM\Table(name="seo_session_url")
 * @ORM\Entity(repositoryClass=SessionUrlRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionUrl
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $leavedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $refererUri;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $uri;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seo\Url", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seo\Session", inversedBy="urls", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLeavedAt(): ?\DateTimeInterface
    {
        return $this->leavedAt;
    }

    public function setLeavedAt(\DateTimeInterface $leavedAt): self
    {
        $this->leavedAt = $leavedAt;

        return $this;
    }

    public function getRefererUri(): ?string
    {
        return $this->refererUri;
    }

    public function setRefererUri(?string $refererUri): self
    {
        $this->refererUri = $refererUri;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getUrl(): ?Url
    {
        return $this->url;
    }

    public function setUrl(?Url $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}
