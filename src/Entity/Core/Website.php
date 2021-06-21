<?php

namespace App\Entity\Core;

use App\Entity\Api\Api;
use App\Entity\BaseEntity;
use App\Entity\Information\Information;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Entity\Seo\SeoConfiguration;
use App\Entity\Seo\Redirection;
use App\Entity\Todo\Todo;
use App\Repository\Core\WebsiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Website
 *
 * @ORM\Table(name="core_website")
 * @ORM\Entity(repositoryClass=WebsiteRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Website extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'website'
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uploadDirname;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cacheClearDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Security", inversedBy="website", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $security;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Information\Information", inversedBy="website", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $information;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Seo\SeoConfiguration", mappedBy="website", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $seoConfiguration;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="website", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $configuration;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Api", mappedBy="website", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $api;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Media", mappedBy="website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $medias;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Folder", mappedBy="website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $folders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Seo\Redirection", mappedBy="website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $redirections;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Todo\Todo", mappedBy="website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $todos;

    /**
     * Website constructor.
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->folders = new ArrayCollection();
        $this->redirections = new ArrayCollection();
        $this->todos = new ArrayCollection();
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getUploadDirname(): ?string
    {
        return $this->uploadDirname;
    }

    public function setUploadDirname(string $uploadDirname): self
    {
        $this->uploadDirname = $uploadDirname;

        return $this;
    }

    public function getCacheClearDate(): ?\DateTimeInterface
    {
        return $this->cacheClearDate;
    }

    public function setCacheClearDate(?\DateTimeInterface $cacheClearDate): self
    {
        $this->cacheClearDate = $cacheClearDate;

        return $this;
    }

    public function getSecurity(): ?Security
    {
        return $this->security;
    }

    public function setSecurity(?Security $security): self
    {
        $this->security = $security;

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

    public function getSeoConfiguration(): ?SeoConfiguration
    {
        return $this->seoConfiguration;
    }

    public function setSeoConfiguration(?SeoConfiguration $seoConfiguration): self
    {
        $this->seoConfiguration = $seoConfiguration;

        return $this;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getApi(): ?Api
    {
        return $this->api;
    }

    public function setApi(?Api $api): self
    {
        $this->api = $api;

        // set (or unset) the owning side of the relation if necessary
        $newWebsite = null === $api ? null : $this;
        if ($api->getWebsite() !== $newWebsite) {
            $api->setWebsite($newWebsite);
        }

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setWebsite($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
            // set the owning side to null (unless already changed)
            if ($media->getWebsite() === $this) {
                $media->setWebsite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Folder[]
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
            $folder->setWebsite($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        if ($this->folders->contains($folder)) {
            $this->folders->removeElement($folder);
            // set the owning side to null (unless already changed)
            if ($folder->getWebsite() === $this) {
                $folder->setWebsite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Redirection[]
     */
    public function getRedirections(): Collection
    {
        return $this->redirections;
    }

    public function addRedirection(Redirection $redirection): self
    {
        if (!$this->redirections->contains($redirection)) {
            $this->redirections[] = $redirection;
            $redirection->setWebsite($this);
        }

        return $this;
    }

    public function removeRedirection(Redirection $redirection): self
    {
        if ($this->redirections->contains($redirection)) {
            $this->redirections->removeElement($redirection);
            // set the owning side to null (unless already changed)
            if ($redirection->getWebsite() === $this) {
                $redirection->setWebsite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Todo[]
     */
    public function getTodos(): Collection
    {
        return $this->todos;
    }

    public function addTodo(Todo $todo): self
    {
        if (!$this->todos->contains($todo)) {
            $this->todos[] = $todo;
            $todo->setWebsite($this);
        }

        return $this;
    }

    public function removeTodo(Todo $todo): self
    {
        if ($this->todos->contains($todo)) {
            $this->todos->removeElement($todo);
            // set the owning side to null (unless already changed)
            if ($todo->getWebsite() === $this) {
                $todo->setWebsite(null);
            }
        }

        return $this;
    }
}
