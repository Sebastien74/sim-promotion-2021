<?php

namespace App\Entity;

use App\Entity\Core\Website;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseListing
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseListing extends BaseEntity
{
    /**
     * @ORM\Column(type="boolean")
     */
    private $hideDate = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayCategory = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayThumbnail = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $largeFirst = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scrollInfinite = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $groupByCategory = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formatDate = "dd/MM/Y";

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $itemsPerPage = 12;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderBy = 'publicationStart-desc';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getHideDate(): ?bool
    {
        return $this->hideDate;
    }

    public function setHideDate(bool $hideDate): self
    {
        $this->hideDate = $hideDate;

        return $this;
    }

    public function getDisplayCategory(): ?bool
    {
        return $this->displayCategory;
    }

    public function setDisplayCategory(bool $displayCategory): self
    {
        $this->displayCategory = $displayCategory;

        return $this;
    }

    public function getDisplayThumbnail(): ?bool
    {
        return $this->displayThumbnail;
    }

    public function setDisplayThumbnail(bool $displayThumbnail): self
    {
        $this->displayThumbnail = $displayThumbnail;

        return $this;
    }

    public function getLargeFirst(): ?bool
    {
        return $this->largeFirst;
    }

    public function setLargeFirst(bool $largeFirst): self
    {
        $this->largeFirst = $largeFirst;

        return $this;
    }

    public function getScrollInfinite(): ?bool
    {
        return $this->scrollInfinite;
    }

    public function setScrollInfinite(bool $scrollInfinite): self
    {
        $this->scrollInfinite = $scrollInfinite;

        return $this;
    }

    public function getGroupByCategory(): ?bool
    {
        return $this->groupByCategory;
    }

    public function setGroupByCategory(bool $groupByCategory): self
    {
        $this->groupByCategory = $groupByCategory;

        return $this;
    }

    public function getFormatDate(): ?string
    {
        return $this->formatDate;
    }

    public function setFormatDate(?string $formatDate): self
    {
        $this->formatDate = $formatDate;

        return $this;
    }

    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(?int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

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