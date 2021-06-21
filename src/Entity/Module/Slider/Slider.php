<?php

namespace App\Entity\Module\Slider;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Repository\Module\Slider\SliderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Slider
 *
 * @ORM\Table(name="module_slider")
 * @ORM\Entity(repositoryClass=SliderRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Slider extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'slider'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $template = 'bootstrap';

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $intervalDuration = 5000;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $itemsPerSlide = 1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $itemsPerSlideMiniPC;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $itemsPerSlideTablet;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $itemsPerSlideMobile;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $effect = 'fade';

    /**
     * @ORM\Column(type="boolean")
     */
    private $banner = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $indicators = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $control = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $autoplay = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pause = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $popup = false;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_slider_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="slider_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Slider constructor.
     */
    public function __construct()
    {
        $this->mediaRelations = new ArrayCollection();
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getIntervalDuration(): ?int
    {
        return $this->intervalDuration;
    }

    public function setIntervalDuration(?int $intervalDuration): self
    {
        $this->intervalDuration = $intervalDuration;

        return $this;
    }

    public function getItemsPerSlide(): ?int
    {
        return $this->itemsPerSlide;
    }

    public function setItemsPerSlide(?int $itemsPerSlide): self
    {
        $this->itemsPerSlide = $itemsPerSlide;

        return $this;
    }

    public function getItemsPerSlideMiniPC(): ?int
    {
        return $this->itemsPerSlideMiniPC;
    }

    public function setItemsPerSlideMiniPC(?int $itemsPerSlideMiniPC): self
    {
        $this->itemsPerSlideMiniPC = $itemsPerSlideMiniPC;

        return $this;
    }

    public function getItemsPerSlideTablet(): ?int
    {
        return $this->itemsPerSlideTablet;
    }

    public function setItemsPerSlideTablet(?int $itemsPerSlideTablet): self
    {
        $this->itemsPerSlideTablet = $itemsPerSlideTablet;

        return $this;
    }

    public function getItemsPerSlideMobile(): ?int
    {
        return $this->itemsPerSlideMobile;
    }

    public function setItemsPerSlideMobile(?int $itemsPerSlideMobile): self
    {
        $this->itemsPerSlideMobile = $itemsPerSlideMobile;

        return $this;
    }

    public function getEffect(): ?string
    {
        return $this->effect;
    }

    public function setEffect(string $effect): self
    {
        $this->effect = $effect;

        return $this;
    }

    public function getBanner(): ?bool
    {
        return $this->banner;
    }

    public function setBanner(bool $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    public function getIndicators(): ?bool
    {
        return $this->indicators;
    }

    public function setIndicators(bool $indicators): self
    {
        $this->indicators = $indicators;

        return $this;
    }

    public function getControl(): ?bool
    {
        return $this->control;
    }

    public function setControl(bool $control): self
    {
        $this->control = $control;

        return $this;
    }

    public function getAutoplay(): ?bool
    {
        return $this->autoplay;
    }

    public function setAutoplay(bool $autoplay): self
    {
        $this->autoplay = $autoplay;

        return $this;
    }

    public function getPause(): ?bool
    {
        return $this->pause;
    }

    public function setPause(bool $pause): self
    {
        $this->pause = $pause;

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

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

        return $this;
    }
}
