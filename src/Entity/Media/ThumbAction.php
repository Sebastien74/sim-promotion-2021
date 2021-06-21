<?php

namespace App\Entity\Media;

use App\Entity\BaseInterface;
use App\Entity\Layout\BlockType;
use App\Repository\Media\ThumbActionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ThumbAction
 *
 * @ORM\Table(name="media_thumb_action")
 * @ORM\Entity(repositoryClass=ThumbActionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbAction extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'thumbaction'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adminName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $namespace;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $actionFilter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\BlockType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $blockType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\ThumbConfiguration", inversedBy="actions", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $configuration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdminName(): ?string
    {
        return $this->adminName;
    }

    public function setAdminName(string $adminName): self
    {
        $this->adminName = $adminName;

        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getActionFilter(): ?string
    {
        return $this->actionFilter;
    }

    public function setActionFilter(?string $actionFilter): self
    {
        $this->actionFilter = $actionFilter;

        return $this;
    }

    public function getConfiguration(): ?ThumbConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?ThumbConfiguration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getBlockType(): ?BlockType
    {
        return $this->blockType;
    }

    public function setBlockType(?BlockType $blockType): self
    {
        $this->blockType = $blockType;

        return $this;
    }
}
