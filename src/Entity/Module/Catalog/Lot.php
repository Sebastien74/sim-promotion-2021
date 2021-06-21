<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Module\Catalog\LotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Lot
 *
 * @ORM\Table(name="module_catalog_product_lot")
 * @ORM\Entity(repositoryClass=LotRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Lot extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'product';
    protected static $interface = [
        'name' => 'cataloglot'
    ];

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $reference;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $surface;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $balconySurface;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sold = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Product", inversedBy="lots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_lot_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="lot_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_catalog_product_lot_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="lot_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Lot constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(?float $surface): self
    {
        $this->surface = $surface;

        return $this;
    }

    public function getBalconySurface(): ?float
    {
        return $this->balconySurface;
    }

    public function setBalconySurface(?float $balconySurface): self
    {
        $this->balconySurface = $balconySurface;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

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

    public function getSold(): ?bool
    {
        return $this->sold;
    }

    public function setSold(bool $sold): self
    {
        $this->sold = $sold;

        return $this;
    }
}
