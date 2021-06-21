<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Layout\BlockTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BlockType
 *
 * @ORM\Table(name="layout_block_type")
 * @ORM\Entity(repositoryClass=BlockTypeRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockType extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'blocktype'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $iconClass;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fieldType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dropdown = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $editable = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inAdvert = false;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="layout_block_type_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="block_type_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    public function setIconClass(string $iconClass): self
    {
        $this->iconClass = $iconClass;

        return $this;
    }

    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    public function setFieldType(?string $fieldType): self
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getDropdown(): ?bool
    {
        return $this->dropdown;
    }

    public function setDropdown(bool $dropdown): self
    {
        $this->dropdown = $dropdown;

        return $this;
    }

    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): self
    {
        $this->editable = $editable;

        return $this;
    }

    public function getInAdvert(): ?bool
    {
        return $this->inAdvert;
    }

    public function setInAdvert(bool $inAdvert): self
    {
        $this->inAdvert = $inAdvert;

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
