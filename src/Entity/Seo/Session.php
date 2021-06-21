<?php

namespace App\Entity\Seo;

use App\Entity\Core\Website;
use App\Repository\Seo\SessionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Session
 *
 * @ORM\Table(name="seo_session")
 * @ORM\Entity(repositoryClass=SessionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Session
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $day;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $screen;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tokenSession;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $lastActivity;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Seo\SessionUrl", mappedBy="session", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $urls;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seo\SessionGroup", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        $this->urls = new ArrayCollection();
    }

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

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getScreen(): ?string
    {
        return $this->screen;
    }

    public function setTokenSession(string $tokenSession): self
    {
        $this->tokenSession = $tokenSession;

        return $this;
    }

    public function getTokenSession(): ?string
    {
        return $this->tokenSession;
    }

    public function setScreen(string $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?\DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    /**
     * @return Collection|SessionUrl[]
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function addUrl(SessionUrl $url): self
    {
        if (!$this->urls->contains($url)) {
            $this->urls[] = $url;
            $url->setSession($this);
        }

        return $this;
    }

    public function removeUrl(SessionUrl $url): self
    {
        if ($this->urls->contains($url)) {
            $this->urls->removeElement($url);
            // set the owning side to null (unless already changed)
            if ($url->getSession() === $this) {
                $url->setSession(null);
            }
        }

        return $this;
    }

    public function getGroup(): ?SessionGroup
    {
        return $this->group;
    }

    public function setGroup(?SessionGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

        return $this;
    }
}
