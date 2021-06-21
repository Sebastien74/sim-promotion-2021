<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\ColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Color
 *
 * @ORM\Table(name="core_color")
 * @ORM\Entity(repositoryClass=ColorRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Color extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'configuration';
    protected static $interface = [
        'name' => 'color'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deletable = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="colors", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

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

    public function getDeletable(): ?bool
    {
        return $this->deletable;
    }

    public function setDeletable(bool $deletable): self
    {
        $this->deletable = $deletable;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
