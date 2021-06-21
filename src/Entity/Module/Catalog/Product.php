<?php

namespace App\Entity\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Media\MediaRelation;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Repository\Module\Catalog\ProductRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\BaseEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="module_catalog_product")
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Product extends BaseEntity
{
    /**
     * @var array
     */
    protected static $masterField = 'catalog';
    protected static $interface = [
        'name' => 'catalogproduct',
        'asCard' => true,
        'search' => true,
        'resize' => true,
        'indexPage' => 'catalog',
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
        "i18ns.body" => "Description",
        "count" => "Valeurs"
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $promote = false;

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
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="catalogproduct", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Catalog\FeatureValueProduct", mappedBy="product", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $values;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Catalog\Lot", mappedBy="product", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $lots;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Catalog\Video", mappedBy="product", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $videos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Catalog", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $catalog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Information\Information", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_product_informations",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="information_id", referencedColumnName="id", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $informations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Category", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_categories",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")},
     * )
     * @Assert\Valid()
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Seo\Url", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_urls",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="url_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $urls;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_relations",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Product", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"adminName"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_products",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="product_child_id", referencedColumnName="id", onDelete="CASCADE")},
     * )
     * @Assert\Valid()
     */
    private $products;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->informations = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->lots = new ArrayCollection();
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
     * @return Collection|FeatureValueProduct[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(FeatureValueProduct $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setProduct($this);
        }

        return $this;
    }

    public function removeValue(FeatureValueProduct $value): self
    {
        if ($this->values->removeElement($value)) {
            // set the owning side to null (unless already changed)
            if ($value->getProduct() === $this) {
                $value->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setProduct($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getProduct() === $this) {
                $video->setProduct(null);
            }
        }

        return $this;
    }

    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    public function setCatalog(?Catalog $catalog): self
    {
        $this->catalog = $catalog;

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
     * @return Collection|Information[]
     */
    public function getInformations(): Collection
    {
        return $this->informations;
    }

    public function addInformation(Information $information): self
    {
        if (!$this->informations->contains($information)) {
            $this->informations[] = $information;
        }

        return $this;
    }

    public function removeInformation(Information $information): self
    {
        $this->informations->removeElement($information);

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * @return Collection|Lot[]
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
            $lot->setProduct($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        if ($this->lots->removeElement($lot)) {
            // set the owning side to null (unless already changed)
            if ($lot->getProduct() === $this) {
                $lot->setProduct(null);
            }
        }

        return $this;
    }
}