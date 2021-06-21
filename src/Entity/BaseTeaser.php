<?php

namespace App\Entity;

use App\Entity\Core\Website;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseTeaser
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseTeaser extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';

    /**
     * @ORM\Column(type="boolean")
     */
    protected $promote = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hasSlider = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    protected $nbrItems = 2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    protected $itemsPerSlide = 1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $orderBy = 'publicationStart-desc';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $website;

    public function getPromote(): ?bool
    {
        return $this->promote;
    }

    public function setPromote(bool $promote): self
    {
        $this->promote = $promote;

        return $this;
    }

    public function getHasSlider(): ?bool
    {
        return $this->hasSlider;
    }

    public function setHasSlider(bool $hasSlider): self
    {
        $this->hasSlider = $hasSlider;

        return $this;
    }

    public function getNbrItems(): ?int
    {
        return $this->nbrItems;
    }

    public function setNbrItems(?int $nbrItems): self
    {
        $this->nbrItems = $nbrItems;

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

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

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