<?php

namespace App\Entity\Layout;

use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Layout\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Zone
 *
 * @ORM\Table(name="layout_zone")
 * @ORM\Entity(repositoryClass=ZoneRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Zone extends BaseConfiguration
{
    /**
     * Configurations
     */
    protected static $masterField = 'layout';
    protected static $interface = [
        'name' => 'zone'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $customId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $customClass;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titlePosition;

    /**
     * @ORM\Column(type="boolean")
     */
    private $backgroundFixed = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $backgroundFullSize = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $backgroundParallax = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $standardizeMedia = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $centerCol = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $centerColsGroup = false;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $grid = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="zones", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\Col", mappedBy="zone", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $cols;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="layout_zone_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="zone_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="layout_zone_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="zone_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * Zone constructor.
     */
    public function __construct()
    {
        $this->cols = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->paddingTop = !$this->paddingTop ? 'pt-lg' : $this->paddingTop;
        $this->paddingRight = !$this->paddingRight ? 'pe-0' : $this->paddingRight;
        $this->paddingBottom = !$this->paddingBottom ? 'pb-lg' : $this->paddingBottom;
        $this->paddingLeft = !$this->paddingLeft ? 'ps-0' : $this->paddingLeft;
    }

    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    public function setCustomId(?string $customId): self
    {
        $this->customId = $customId;

        return $this;
    }

    public function getCustomClass(): ?string
    {
        return $this->customClass;
    }

    public function setCustomClass(?string $customClass): self
    {
        $this->customClass = $customClass;

        return $this;
    }

    public function getTitlePosition(): ?string
    {
        return $this->titlePosition;
    }

    public function setTitlePosition(?string $titlePosition): self
    {
        $this->titlePosition = $titlePosition;

        return $this;
    }

    public function getBackgroundFixed(): ?bool
    {
        return $this->backgroundFixed;
    }

    public function setBackgroundFixed(bool $backgroundFixed): self
    {
        $this->backgroundFixed = $backgroundFixed;

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

    public function getBackgroundParallax(): ?bool
    {
        return $this->backgroundParallax;
    }

    public function setBackgroundParallax(bool $backgroundParallax): self
    {
        $this->backgroundParallax = $backgroundParallax;

        return $this;
    }

    public function getStandardizeMedia(): ?bool
    {
        return $this->standardizeMedia;
    }

    public function setStandardizeMedia(bool $standardizeMedia): self
    {
        $this->standardizeMedia = $standardizeMedia;

        return $this;
    }

    public function getCenterCol(): ?bool
    {
        return $this->centerCol;
    }

    public function setCenterCol(bool $centerCol): self
    {
        $this->centerCol = $centerCol;

        return $this;
    }


    public function getCenterColsGroup(): ?bool
    {
        return $this->centerColsGroup;
    }

    public function setCenterColsGroup(bool $centerColsGroup): self
    {
        $this->centerColsGroup = $centerColsGroup;

        return $this;
    }

    public function getGrid(): ?array
    {
        return $this->grid;
    }

    public function setGrid(?array $grid): self
    {
        $this->grid = $grid;

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
     * @return Collection|Col[]
     */
    public function getCols(): Collection
    {
        return $this->cols;
    }

    public function addCol(Col $col): self
    {
        if (!$this->cols->contains($col)) {
            $this->cols[] = $col;
            $col->setZone($this);
        }

        return $this;
    }

    public function removeCol(Col $col): self
    {
        if ($this->cols->contains($col)) {
            $this->cols->removeElement($col);
            // set the owning side to null (unless already changed)
            if ($col->getZone() === $this) {
                $col->setZone(null);
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
