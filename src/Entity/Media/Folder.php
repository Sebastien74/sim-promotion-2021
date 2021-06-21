<?php

namespace App\Entity\Media;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Media\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Folder
 *
 * @ORM\Table(name="media_folder")
 * @ORM\Entity(repositoryClass=FolderRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Folder extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'folder'
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private $level = 1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $webmaster = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deletable = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Media", mappedBy="folder", fetch="EXTRA_LAZY")
     */
    private $medias;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Folder", mappedBy="parent", fetch="EXTRA_LAZY")
     */
    private $folders;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", inversedBy="folders", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Folder", inversedBy="folders", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * Folder constructor.
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection();
        $this->folders = new ArrayCollection();
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getWebmaster(): ?bool
    {
        return $this->webmaster;
    }

    public function setWebmaster(bool $webmaster): self
    {
        $this->webmaster = $webmaster;

        return $this;
    }

    public function getDeletable(): ?bool
    {
        return $this->deletable;
    }

    public function setDeletable(bool $deletable): self
    {
        $this->deletable = $deletable;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setFolder($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
            // set the owning side to null (unless already changed)
            if ($media->getFolder() === $this) {
                $media->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Folder[]
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
            $folder->setParent($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        if ($this->folders->contains($folder)) {
            $this->folders->removeElement($folder);
            // set the owning side to null (unless already changed)
            if ($folder->getParent() === $this) {
                $folder->setParent(null);
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
