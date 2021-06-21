<?php

namespace App\Entity\Layout;

use App\Entity\Media\MediaRelation;
use App\Repository\Layout\ColRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Col
 *
 * @ORM\Table(name="layout_col")
 * @ORM\Entity(repositoryClass=ColRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Col extends BaseConfiguration
{
    /**
     * Configurations
     */
    protected static $masterField = 'zone';
    protected static $interface = [
        'name' => 'col'
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private $size = 12;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $mobileSize;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tabletSize;

    /**
     * @ORM\Column(type="boolean")
     */
    private $backgroundFullSize = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Zone", inversedBy="cols", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $zone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\Block", mappedBy="col", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $blocks;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="layout_col_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="col_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Layout constructor.
     */
    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMobileSize(): ?int
    {
        return $this->mobileSize;
    }

    public function setMobileSize(?int $mobileSize): self
    {
        $this->mobileSize = $mobileSize;

        return $this;
    }

    public function getTabletSize(): ?int
    {
        return $this->tabletSize;
    }

    public function setTabletSize(?int $tabletSize): self
    {
        $this->tabletSize = $tabletSize;

        return $this;
    }

    public function getBackgroundFullSize(): ?bool
    {
        return $this->backgroundFullSize;
    }

    public function setBackgroundFullSize(bool $backgroundFullSize): self
    {
        $this->backgroundFullSize = $backgroundFullSize;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @return Collection|Block[]
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(Block $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks[] = $block;
            $block->setCol($this);
        }

        return $this;
    }

    public function removeBlock(Block $block): self
    {
        if ($this->blocks->contains($block)) {
            $this->blocks->removeElement($block);
            // set the owning side to null (unless already changed)
            if ($block->getCol() === $this) {
                $block->setCol(null);
            }
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