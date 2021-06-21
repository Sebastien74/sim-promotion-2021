<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseTeaser;
use App\Entity\Translation\i18n;
use App\Repository\Module\Catalog\TeaserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Teaser
 *
 * @ORM\Table(name="module_catalog_product_teaser")
 * @ORM\Entity(repositoryClass=TeaserRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Teaser extends BaseTeaser
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'productteaser'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Catalog", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_product_teaser_catalogs",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="catalog_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $catalogs;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Catalog\Category", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_catalog_product_teaser_categories",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_teaser_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Teaser constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
        $this->catalogs = new ArrayCollection();
        $this->categories = new ArrayCollection();
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
        $this->categories->removeElement($category);

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
}