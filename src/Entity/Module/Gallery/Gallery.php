<?php

namespace App\Entity\Module\Gallery;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Repository\Module\Gallery\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gallery
 *
 * @ORM\Table(name="module_gallery")
 * @ORM\Entity(repositoryClass=GalleryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Gallery extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'gallery'
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrColumn = 3;

    /**
     * @ORM\Column(type="boolean")
     */
    private $popup = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Gallery\Category", inversedBy="galleries", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Assert\Valid()
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_gallery_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="gallery_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * Gallery constructor.
     */
    public function __construct()
    {
        $this->mediaRelations = new ArrayCollection();
    }

    public function getNbrColumn(): ?int
    {
        return $this->nbrColumn;
    }

    public function setNbrColumn(int $nbrColumn): self
    {
        $this->nbrColumn = $nbrColumn;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
