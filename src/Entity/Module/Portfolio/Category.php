<?php

namespace App\Entity\Module\Portfolio;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Media\MediaRelation;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Repository\Module\Portfolio\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="module_portfolio_category")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Category extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'portfoliocategory',
        'search' => true,
        'resize' => true,
        'indexPage' => 'categories',
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationEnd;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_portfolio_category_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Seo\Url", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_portfolio_category_urls",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="url_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $urls;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_portfolio_category_relations",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->urls = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
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

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;

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
        $this->i18ns->removeElement($i18n);

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
        $this->urls->removeElement($url);

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
        $this->mediaRelations->removeElement($mediaRelation);

        return $this;
    }
}
