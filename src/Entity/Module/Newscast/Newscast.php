<?php

namespace App\Entity\Module\Newscast;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Media\MediaRelation;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Repository\Module\Newscast\NewscastRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Newscast
 *
 * @ORM\Table(name="module_newscast")
 * @ORM\Entity(repositoryClass=NewscastRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Newscast extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = "website";
    protected static $interface = [
        'name' => 'newscast',
        'asCard' => true,
        'search' => true,
        'resize' => true,
        'indexPage' => 'category',
        'listingClass' => Listing::class,
        'seo' => [
            'i18ns.title',
            'i18ns.introduction',
            'i18ns.body'
        ]
    ];
    protected static $labels = [
        "i18ns.title" => "Titre",
        "i18ns.introduction" => "Introduction",
        "i18ns.body" => "Description"
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $promote = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationEnd;

    /**
     * @ORM\Column(type="boolean")
     */
    private $customLayout = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="newscast", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Newscast\Comment", mappedBy="newscast", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"createdAt"="DESC"})
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Newscast\Category", inversedBy="newscasts", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Assert\Valid()
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_newscast_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="newscast_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Seo\Url", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_newscast_urls",
     *      joinColumns={@ORM\JoinColumn(name="newscast_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="url_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $urls;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_newscast_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="newscast_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Newscast\Tag", inversedBy="newscasts", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_newscasts_tags")
     */
    private $tags;

    /**
     * Newscast constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->publicationStart)) {
            $this->publicationStart = new DateTime();
        }

        parent::prePersist();
    }

    public function getPromote(): ?bool
    {
        return $this->promote;
    }

    public function setPromote(bool $promote): self
    {
        $this->promote = $promote;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

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

    public function getCustomLayout(): ?bool
    {
        return $this->customLayout;
    }

    public function setCustomLayout(bool $customLayout): self
    {
        $this->customLayout = $customLayout;

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

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setNewscast($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getNewscast() === $this) {
                $comment->setNewscast(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
     * @return Collection|i18n[]
     */
    public function getI18ns(): Collection
    {
        return $this->i18ns;
    }

    public function addI18n(i18n $i18n): self
    {
        if (!$this->i18ns->contains($i18n)) {
            $this->i18ns[] = $i18n;
        }

        return $this;
    }

    public function removeI18n(i18n $i18n): self
    {
        if ($this->i18ns->contains($i18n)) {
            $this->i18ns->removeElement($i18n);
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

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }
}
