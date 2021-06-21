<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Module\Catalog\FeatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Feature
 *
 * @ORM\Table(name="module_catalog_feature")
 * @ORM\Entity(repositoryClass=FeatureRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Feature extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'catalogfeature',
        'buttons' => [
            'values' => 'admin_catalogfeaturevalue_index'
        ],
    ];
    protected static $labels = [
        "admin_catalogfeaturevalue_index" => "Valeurs"
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Catalog\FeatureValue", mappedBy="catalogfeature", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"adminName"="ASC"})
     * @Assert\Valid()
     */
    private $values;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_feature_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="feature_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_feature_relations",
     *      joinColumns={@ORM\JoinColumn(name="feature_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Feature constructor.
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    /**
     * @return Collection|FeatureValue[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(FeatureValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setCatalogfeature($this);
        }

        return $this;
    }

    public function removeValue(FeatureValue $value): self
    {
        if ($this->values->contains($value)) {
            $this->values->removeElement($value);
            // set the owning side to null (unless already changed)
            if ($value->getCatalogfeature() === $this) {
                $value->setCatalogfeature(null);
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