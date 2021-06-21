<?php

namespace App\Entity\Module\Map;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Map\MapRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Map
 *
 * @ORM\Table(name="module_map")
 * @ORM\Entity(repositoryClass=MapRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Map extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'map',
        'buttons' => [
            'points' => 'admin_mappoint_index'
        ]
    ];
    protected static $labels = [
        "admin_mappoint_index" => "Points"
    ];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $layer;

    /**
     * @ORM\Column(type="integer")
     */
    private $zoom = 11;

    /**
     * @ORM\Column(type="integer")
     */
    private $minZoom = 5;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxZoom = 20;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $latitude = 45.899247;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $longitude = 6.129384;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayFilters = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $markerClusters = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $multiFilters = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefault = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Map\Point", mappedBy="map", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Map constructor.
     */
    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getLayer(): ?string
    {
        return $this->layer;
    }

    public function setLayer(?string $layer): self
    {
        $this->layer = $layer;

        return $this;
    }

    public function getZoom(): ?int
    {
        return $this->zoom;
    }

    public function setZoom(int $zoom): self
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getMinZoom(): ?int
    {
        return $this->minZoom;
    }

    public function setMinZoom(int $minZoom): self
    {
        $this->minZoom = $minZoom;

        return $this;
    }

    public function getMaxZoom(): ?int
    {
        return $this->maxZoom;
    }

    public function setMaxZoom(int $maxZoom): self
    {
        $this->maxZoom = $maxZoom;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getDisplayFilters(): ?bool
    {
        return $this->displayFilters;
    }

    public function setDisplayFilters(bool $displayFilters): self
    {
        $this->displayFilters = $displayFilters;

        return $this;
    }

    public function getMarkerClusters(): ?bool
    {
        return $this->markerClusters;
    }

    public function setMarkerClusters(bool $markerClusters): self
    {
        $this->markerClusters = $markerClusters;

        return $this;
    }

    public function getMultiFilters(): ?bool
    {
        return $this->multiFilters;
    }

    public function setMultiFilters(bool $multiFilters): self
    {
        $this->multiFilters = $multiFilters;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setMap($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getMap() === $this) {
                $point->setMap(null);
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
}
