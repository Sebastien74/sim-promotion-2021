<?php

namespace App\Entity\Module\Menu;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Menu\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu
 *
 * @ORM\Table(name="module_menu")
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 * @ORM\Cache(usage = "READ_ONLY")
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Menu extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = "website";
    protected static $interface = [
        'name' => 'menu'
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFooter = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fixedOnScroll = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dropdownHover = true;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $template = 'bootstrap';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $expand = 'lg';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $size = 'container';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $alignment = 'left';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxLevel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", nullable=false)
     */
    private $website;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Menu\Link", mappedBy="menu", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $links;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->links = new ArrayCollection();
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

    public function getIsFooter(): ?bool
    {
        return $this->isFooter;
    }

    public function setIsFooter(bool $isFooter): self
    {
        $this->isFooter = $isFooter;

        return $this;
    }

    public function getFixedOnScroll(): ?bool
    {
        return $this->fixedOnScroll;
    }

    public function setFixedOnScroll(bool $fixedOnScroll): self
    {
        $this->fixedOnScroll = $fixedOnScroll;

        return $this;
    }

    public function getDropdownHover(): ?bool
    {
        return $this->dropdownHover;
    }

    public function setDropdownHover(bool $dropdownHover): self
    {
        $this->dropdownHover = $dropdownHover;

        return $this;
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

    public function getExpand(): ?string
    {
        return $this->expand;
    }

    public function setExpand(string $expand): self
    {
        $this->expand = $expand;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }

    public function setAlignment(string $alignment): self
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function getMaxLevel(): ?int
    {
        return $this->maxLevel;
    }

    public function setMaxLevel(?int $maxLevel): self
    {
        $this->maxLevel = $maxLevel;

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
            $link->setMenu($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
            // set the owning side to null (unless already changed)
            if ($link->getMenu() === $this) {
                $link->setMenu(null);
            }
        }

        return $this;
    }
}
