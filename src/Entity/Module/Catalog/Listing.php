<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Catalog\ListingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Listing
 *
 * @ORM\Table(name="module_catalog_listing")
 * @ORM\Entity(repositoryClass=ListingRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Listing extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'cataloglisting'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $display = 'configuration';

    /**
     * @ORM\Column(type="boolean")
     */
    private $searchText = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $counter = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $searchCatalogs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $searchCategories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $searchFeatures;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $orderBy = 'position';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $orderSort = 'ASC';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Catalog\ListingFeatureValue", mappedBy="listing", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $featuresValues;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Catalog", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_listing_catalogs",
     *      joinColumns={@ORM\JoinColumn(name="listing_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="catalog_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $catalogs;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Category", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_listings_categories",
     *      joinColumns={@ORM\JoinColumn(name="listing_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Feature", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_listings_features",
     *      joinColumns={@ORM\JoinColumn(name="listing_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $features;

    /**
     * Listing constructor.
     */
    public function __construct()
    {
        $this->featuresValues = new ArrayCollection();
        $this->catalogs = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->features = new ArrayCollection();
    }

    public function getDisplay(): ?string
    {
        return $this->display;
    }

    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getSearchText(): ?bool
    {
        return $this->searchText;
    }

    public function setSearchText(bool $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    public function getCounter(): ?bool
    {
        return $this->counter;
    }

    public function setCounter(bool $counter): self
    {
        $this->counter = $counter;

        return $this;
    }

    public function getSearchCatalogs(): ?string
    {
        return $this->searchCatalogs;
    }

    public function setSearchCatalogs(?string $searchCatalogs): self
    {
        $this->searchCatalogs = $searchCatalogs;

        return $this;
    }

    public function getSearchCategories(): ?string
    {
        return $this->searchCategories;
    }

    public function setSearchCategories(?string $searchCategories): self
    {
        $this->searchCategories = $searchCategories;

        return $this;
    }

    public function getSearchFeatures(): ?string
    {
        return $this->searchFeatures;
    }

    public function setSearchFeatures(?string $searchFeatures): self
    {
        $this->searchFeatures = $searchFeatures;

        return $this;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getOrderSort(): ?string
    {
        return $this->orderSort;
    }

    public function setOrderSort(string $orderSort): self
    {
        $this->orderSort = $orderSort;

        return $this;
    }

    /**
     * @return Collection|ListingFeatureValue[]
     */
    public function getFeaturesValues(): Collection
    {
        return $this->featuresValues;
    }

    public function addFeaturesValue(ListingFeatureValue $featuresValue): self
    {
        if (!$this->featuresValues->contains($featuresValue)) {
            $this->featuresValues[] = $featuresValue;
            $featuresValue->setListing($this);
        }

        return $this;
    }

    public function removeFeaturesValue(ListingFeatureValue $featuresValue): self
    {
        if ($this->featuresValues->contains($featuresValue)) {
            $this->featuresValues->removeElement($featuresValue);
            // set the owning side to null (unless already changed)
            if ($featuresValue->getListing() === $this) {
                $featuresValue->setListing(null);
            }
        }

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
     * @return Collection|Catalog[]
     */
    public function getCatalogs(): Collection
    {
        return $this->catalogs;
    }

    public function addCatalog(Catalog $catalog): self
    {
        if (!$this->catalogs->contains($catalog)) {
            $this->catalogs[] = $catalog;
        }

        return $this;
    }

    public function removeCatalog(Catalog $catalog): self
    {
        if ($this->catalogs->contains($catalog)) {
            $this->catalogs->removeElement($catalog);
        }

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
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return Collection|Feature[]
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features[] = $feature;
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $this->features->removeElement($feature);
        }

        return $this;
    }
}