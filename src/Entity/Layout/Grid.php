<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Repository\Layout\GridRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Grid
 *
 * @ORM\Table(name="layout_grid")
 * @ORM\Entity(repositoryClass=GridRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Grid extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'grid'
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\GridCol", mappedBy="grid", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $cols;

    /**
     * Grid constructor.
     */
    public function __construct()
    {
        $this->cols = new ArrayCollection();
    }

    /**
     * @return Collection|GridCol[]
     */
    public function getCols(): Collection
    {
        return $this->cols;
    }

    public function addCol(GridCol $col): self
    {
        if (!$this->cols->contains($col)) {
            $this->cols[] = $col;
            $col->setGrid($this);
        }

        return $this;
    }

    public function removeCol(GridCol $col): self
    {
        if ($this->cols->contains($col)) {
            $this->cols->removeElement($col);
            // set the owning side to null (unless already changed)
            if ($col->getGrid() === $this) {
                $col->setGrid(null);
            }
        }

        return $this;
    }
}
