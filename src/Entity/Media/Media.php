<?php

namespace App\Entity\Media;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Media\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Media
 *
 * @ORM\Table(name="media", indexes={
 *     @Index(columns={"filename"}, flags={"fulltext"}),
 *     @Index(columns={"name"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Media extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'media'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extension;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $copyright;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titlePosition = 'bottom';

    /**
     * @ORM\Column(type="boolean")
     */
    private $notContractual = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $screen = 'desktop';

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $quality = 100;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Thumb", mappedBy="media", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $thumbs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\MediaRelation", mappedBy="media", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"locale"="ASC"})
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Media", mappedBy="media", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"screen"="ASC"})
     * @Assert\Valid()
     */
    private $mediaScreens;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="media_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Folder", inversedBy="medias", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $folder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", inversedBy="medias", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Media", inversedBy="mediaScreens", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $media;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\Category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="core_media_categories",
     *      joinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $categories;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->thumbs = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->mediaScreens = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFilename();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): self
    {
        $this->copyright = $copyright;

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

    public function getTitlePosition(): ?string
    {
        return $this->titlePosition;
    }

    public function setTitlePosition(?string $titlePosition): self
    {
        $this->titlePosition = $titlePosition;

        return $this;
    }

    public function getNotContractual(): ?bool
    {
        return $this->notContractual;
    }

    public function setNotContractual(bool $notContractual): self
    {
        $this->notContractual = $notContractual;

        return $this;
    }

    public function getScreen(): ?string
    {
        return $this->screen;
    }

    public function setScreen(string $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * @return Collection|Thumb[]
     */
    public function getThumbs(): Collection
    {
        return $this->thumbs;
    }

    public function addThumb(Thumb $thumb): self
    {
        if (!$this->thumbs->contains($thumb)) {
            $this->thumbs[] = $thumb;
            $thumb->setMedia($this);
        }

        return $this;
    }

    public function removeThumb(Thumb $thumb): self
    {
        if ($this->thumbs->contains($thumb)) {
            $this->thumbs->removeElement($thumb);
            // set the owning side to null (unless already changed)
            if ($thumb->getMedia() === $this) {
                $thumb->setMedia(null);
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
            $mediaRelation->setMedia($this);
        }

        return $this;
    }

    public function removeMediaRelation(MediaRelation $mediaRelation): self
    {
        if ($this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations->removeElement($mediaRelation);
            // set the owning side to null (unless already changed)
            if ($mediaRelation->getMedia() === $this) {
                $mediaRelation->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMediaScreens(): Collection
    {
        return $this->mediaScreens;
    }

    public function addMediaScreen(Media $mediaScreen): self
    {
        if (!$this->mediaScreens->contains($mediaScreen)) {
            $this->mediaScreens[] = $mediaScreen;
            $mediaScreen->setMedia($this);
        }

        return $this;
    }

    public function removeMediaScreen(Media $mediaScreen): self
    {
        if ($this->mediaScreens->contains($mediaScreen)) {
            $this->mediaScreens->removeElement($mediaScreen);
            // set the owning side to null (unless already changed)
            if ($mediaScreen->getMedia() === $this) {
                $mediaScreen->setMedia(null);
            }
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

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

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

    public function getMedia(): ?self
    {
        return $this->media;
    }

    public function setMedia(?self $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}