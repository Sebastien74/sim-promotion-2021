<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Entity\Seo\Url;
use App\Repository\Layout\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Page
 *
 * @ORM\Table(name="content_page")
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Page extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'page',
        'search' => true,
        'resize' => true
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isIndex = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $infill = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSecure = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $level = 1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationEnd;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $template = 'cms.html.twig';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deletable = true;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="page", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\Page", mappedBy="parent", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $pages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Page", inversedBy="pages", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $parent;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Seo\Url", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="content_page_urls",
     *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="url_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $urls;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="content_page_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getIsIndex(): ?bool
    {
        return $this->isIndex;
    }

    public function setIsIndex(bool $isIndex): self
    {
        $this->isIndex = $isIndex;

        return $this;
    }

    public function getInfill(): ?bool
    {
        return $this->infill;
    }

    public function setInfill(bool $infill): self
    {
        $this->infill = $infill;

        return $this;
    }

    public function isSecure(): ?bool
    {
        return $this->isSecure;
    }

    public function getIsSecure(): ?bool
    {
        return $this->isSecure;
    }

    public function setIsSecure(bool $isSecure): self
    {
        $this->isSecure = $isSecure;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getPublicationStart(): ?\DateTimeInterface
    {
        return $this->publicationStart;
    }

    public function setPublicationStart(?\DateTimeInterface $publicationStart): self
    {
        $this->publicationStart = $publicationStart;

        return $this;
    }

    public function getPublicationEnd(): ?\DateTimeInterface
    {
        return $this->publicationEnd;
    }

    public function setPublicationEnd(?\DateTimeInterface $publicationEnd): self
    {
        $this->publicationEnd = $publicationEnd;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getDeletable(): ?bool
    {
        return $this->deletable;
    }

    public function setDeletable(bool $deletable): self
    {
        $this->deletable = $deletable;

        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

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

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setParent($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            // set the owning side to null (unless already changed)
            if ($page->getParent() === $this) {
                $page->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Url[]
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function addUrl(Url $url): self
    {
        if (!$this->urls->contains($url)) {
            $this->urls[] = $url;
        }

        return $this;
    }

    public function removeUrl(Url $url): self
    {
        if ($this->urls->contains($url)) {
            $this->urls->removeElement($url);
        }

        return $this;
    }

    /**
     * @return Collection|MediaRelation[]
     */
    public function getMediaRelations(): Collection
    {
        return $this->mediaRelations;
    }

    public function addMediaRelation(MediaRelation $mediaRelation): self
    {
        if (!$this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations[] = $mediaRelation;
        }

        return $this;
    }

    public function removeMediaRelation(MediaRelation $mediaRelation): self
    {
        if ($this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations->removeElement($mediaRelation);
        }

        return $this;
    }
}
