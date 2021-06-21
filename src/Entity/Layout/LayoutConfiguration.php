<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Repository\Layout\LayoutConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * LayoutConfiguration
 *
 * @ORM\Table(name="layout_configuration")
 * @ORM\Entity(repositoryClass=LayoutConfigurationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutConfiguration extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'layoutconfiguration'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Layout\BlockType", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="layout_configuration_block_types",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="block_type_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $blockTypes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Core\Module", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="layout_configuration_modules",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $modules;

    /**
     * LayoutConfiguration constructor.
     */
    public function __construct()
    {
        $this->blockTypes = new ArrayCollection();
        $this->modules = new ArrayCollection();
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

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
     * @return Collection|BlockType[]
     */
    public function getBlockTypes(): Collection
    {
        return $this->blockTypes;
    }

    public function addBlockType(BlockType $blockType): self
    {
        if (!$this->blockTypes->contains($blockType)) {
            $this->blockTypes[] = $blockType;
        }

        return $this;
    }

    public function removeBlockType(BlockType $blockType): self
    {
        if ($this->blockTypes->contains($blockType)) {
            $this->blockTypes->removeElement($blockType);
        }

        return $this;
    }

    /**
     * @return Collection|Module[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules[] = $module;
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        if ($this->modules->contains($module)) {
            $this->modules->removeElement($module);
        }

        return $this;
    }
}
