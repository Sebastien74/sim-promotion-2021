<?php

namespace App\Entity\Media;

use App\Entity\BaseInterface;
use App\Repository\Media\ThumbRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Thumb
 *
 * @ORM\Table(name="media_thumb")
 * @ORM\Entity(repositoryClass=ThumbRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Thumb extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'thumb'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer")
     */
    private $dataX;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dataY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rotate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $scaleX;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $scaleY;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Media", inversedBy="thumbs", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $media;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\ThumbConfiguration", inversedBy="thumbs", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $configuration;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDataX(): ?int
    {
        return $this->dataX;
    }

    public function setDataX(int $dataX): self
    {
        $this->dataX = $dataX;

        return $this;
    }

    public function getDataY(): ?int
    {
        return $this->dataY;
    }

    public function setDataY(?int $dataY): self
    {
        $this->dataY = $dataY;

        return $this;
    }

    public function getRotate(): ?int
    {
        return $this->rotate;
    }

    public function setRotate(?int $rotate): self
    {
        $this->rotate = $rotate;

        return $this;
    }

    public function getScaleX(): ?int
    {
        return $this->scaleX;
    }

    public function setScaleX(?int $scaleX): self
    {
        $this->scaleX = $scaleX;

        return $this;
    }

    public function getScaleY(): ?int
    {
        return $this->scaleY;
    }

    public function setScaleY(?int $scaleY): self
    {
        $this->scaleY = $scaleY;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

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
}
