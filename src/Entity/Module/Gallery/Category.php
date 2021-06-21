<?php

namespace App\Entity\Module\Gallery;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Module\Gallery\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="module_gallery_category")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Category extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'gallerycategory'
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefault = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayCategory = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scrollInfinite = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formatDate = "dd/MM/Y";

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $itemsPerGallery = 24;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Gallery\Gallery", mappedBy="category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $galleries;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_gallery_category_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="gallery_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->galleries = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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

    public function getScrollInfinite(): ?bool
    {
        return $this->scrollInfinite;
    }

    public function setScrollInfinite(bool $scrollInfinite): self
    {
        $this->scrollInfinite = $scrollInfinite;

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

    public function getItemsPerGallery(): ?int
    {
        return $this->itemsPerGallery;
    }

    public function setItemsPerGallery(?int $itemsPerGallery): self
    {
        $this->itemsPerGallery = $itemsPerGallery;

        return $this;
    }

    /**
     * @return Collection|Gallery[]
     */
    public function getGalleries(): Collection
    {
        return $this->galleries;
    }

    public function addGallery(Gallery $gallery): self
    {
        if (!$this->galleries->contains($gallery)) {
            $this->galleries[] = $gallery;
            $gallery->setCategory($this);
        }

        return $this;
    }

    public function removeGallery(Gallery $gallery): self
    {
        if ($this->galleries->contains($gallery)) {
            $this->galleries->removeElement($gallery);
            // set the owning side to null (unless already changed)
            if ($gallery->getCategory() === $this) {
                $gallery->setCategory(null);
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
}