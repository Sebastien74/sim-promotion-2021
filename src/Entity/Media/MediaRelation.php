<?php

namespace App\Entity\Media;

use App\Entity\BaseInterface;
use App\Entity\Translation\i18n;
use App\Repository\Media\MediaRelationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * MediaRelation
 *
 * @ORM\Table(name="media_relation")
 * @ORM\Entity(repositoryClass=MediaRelationRepository::class)
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelation extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'mediarelation',
        'search' => true
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayTitle = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $popup = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxWidth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxHeight;

    /**
     * @ORM\Column(type="integer")
     */
    private $position = 1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $downloadable = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInit = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $i18n;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Media", inversedBy="mediaRelations", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $media;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDisplayTitle(): ?bool
    {
        return $this->displayTitle;
    }

    public function setDisplayTitle(bool $displayTitle): self
    {
        $this->displayTitle = $displayTitle;

        return $this;
    }

    public function getPopup(): ?bool
    {
        return $this->popup;
    }

    public function setPopup(bool $popup): self
    {
        $this->popup = $popup;

        return $this;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function setMaxWidth(?int $maxWidth): self
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function setMaxHeight(?int $maxHeight): self
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDownloadable(): ?bool
    {
        return $this->downloadable;
    }

    public function setDownloadable(bool $downloadable): self
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    public function getIsInit(): ?bool
    {
        return $this->isInit;
    }

    public function setIsInit(bool $isInit): self
    {
        $this->isInit = $isInit;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getI18n(): ?i18n
    {
        return $this->i18n;
    }

    public function setI18n(?i18n $i18n): self
    {
        $this->i18n = $i18n;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }
}
