<?php

namespace App\Entity\Module\Menu;

use App\Entity\BaseEntity;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Module\Menu\LinkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Link
 *
 * @ORM\Table(name="module_menu_link")
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Link extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = "menu";
    protected static $interface = [
        'name' => 'link'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="integer")
     */
    private $level = 1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $btnColor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $i18n;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="media_relation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $mediaRelation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Menu\Menu", inversedBy="links", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $menu;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Menu\Link", mappedBy="parent", fetch="EXTRA_LAZY")
     */
    private $links;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Menu\Link", inversedBy="links", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * Link constructor.
     */
    public function __construct()
    {
        $this->links = new ArrayCollection();
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getBtnColor(): ?string
    {
        return $this->btnColor;
    }

    public function setBtnColor(?string $btnColor): self
    {
        $this->btnColor = $btnColor;

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

    public function getMediaRelation(): ?MediaRelation
    {
        return $this->mediaRelation;
    }

    public function setMediaRelation(?MediaRelation $mediaRelation): self
    {
        $this->mediaRelation = $mediaRelation;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return Collection|Link[]
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links[] = $link;
            $link->setParent($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
            // set the owning side to null (unless already changed)
            if ($link->getParent() === $this) {
                $link->setParent(null);
            }
        }

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
