<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Module\Catalog\FeatureValueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FeatureValue
 *
 * @ORM\Table(name="module_catalog_feature_value")
 * @ORM\Entity(repositoryClass=FeatureValueRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValue extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'catalogfeature';
    protected static $interface = [
        'name' => 'catalogfeaturevalue'
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCustomized = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $iconClass;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Feature", inversedBy="values")
     * @ORM\JoinColumn(nullable=false)
     */
    private $catalogfeature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Product")
     * @ORM\JoinColumn(nullable=true)
     */
    private $product;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_feature_value_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_feature_value_relations",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * FeatureValue constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getIsCustomized(): ?bool
    {
        return $this->isCustomized;
    }

    public function setIsCustomized(bool $isCustomized): self
    {
        $this->isCustomized = $isCustomized;

        return $this;
    }

    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    public function setIconClass(string $iconClass): self
    {
        $this->iconClass = $iconClass;

        return $this;
    }

    public function getCatalogfeature(): ?Feature
    {
        return $this->catalogfeature;
    }

    public function setCatalogfeature(?Feature $catalogfeature): self
    {
        $this->catalogfeature = $catalogfeature;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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