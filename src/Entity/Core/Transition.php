<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\TransitionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Transition
 *
 * @ORM\Table(name="core_transition")
 * @ORM\Entity(repositoryClass=TransitionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Transition extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'configuration';
    protected static $interface = [
        'name' => 'transition'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $section;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $element;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $laxPreset = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aosEffect;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $delay;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $offset;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parameters;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="transitions", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function getLaxPreset(): ?array
    {
        return $this->laxPreset;
    }

    public function setLaxPreset(?array $laxPreset): self
    {
        $this->laxPreset = $laxPreset;

        return $this;
    }

    public function getAosEffect(): ?string
    {
        return $this->aosEffect;
    }

    public function setAosEffect(?string $aosEffect): self
    {
        $this->aosEffect = $aosEffect;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDelay(): ?string
    {
        return $this->delay;
    }

    public function setDelay(?string $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    public function getOffset(): ?string
    {
        return $this->offset;
    }

    public function setOffset(?string $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(?string $parameters): self
    {
        $this->parameters = $parameters;

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
