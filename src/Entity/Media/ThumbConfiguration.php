<?php

namespace App\Entity\Media;

use App\Entity\BaseEntity;
use App\Entity\Core\Configuration;
use App\Repository\Media\ThumbConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ThumbConfiguration
 *
 * @ORM\Table(name="media_thumb_configuration")
 * @ORM\Entity(repositoryClass=ThumbConfigurationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbConfiguration extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'configuration';
    protected static $interface = [
        'name' => 'thumbconfiguration'
    ];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\ThumbAction", mappedBy="configuration", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media\Thumb", mappedBy="configuration", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $thumbs;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    /**
     * ThumbConfiguration constructor.
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->thumbs = new ArrayCollection();
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Collection|ThumbAction[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(ThumbAction $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setConfiguration($this);
        }

        return $this;
    }

    public function removeAction(ThumbAction $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getConfiguration() === $this) {
                $action->setConfiguration(null);
            }
        }

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
            $thumb->setConfiguration($this);
        }

        return $this;
    }

    public function removeThumb(Thumb $thumb): self
    {
        if ($this->thumbs->contains($thumb)) {
            $this->thumbs->removeElement($thumb);
            // set the owning side to null (unless already changed)
            if ($thumb->getConfiguration() === $this) {
                $thumb->setConfiguration(null);
            }
        }

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
