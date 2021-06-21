<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Repository\Layout\GridColRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Grid
 *
 * @ORM\Table(name="layout_grid_col")
 * @ORM\Entity(repositoryClass=GridColRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridCol extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'grid';
    protected static $interface = [
        'name' => 'gridcol'
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private $size = 12;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Grid", inversedBy="cols", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $grid;

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getGrid(): ?Grid
    {
        return $this->grid;
    }

    public function setGrid(?Grid $grid): self
    {
        $this->grid = $grid;

        return $this;
    }
}
