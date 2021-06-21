<?php

namespace App\Entity\Module\Table;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Table\TableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Table
 *
 * @ORM\Table(name="module_table")
 * @ORM\Entity(repositoryClass=TableRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Table extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'table'
    ];

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $headBackgroundColor;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $headColor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Table\Col", mappedBy="table", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $cols;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->cols = new ArrayCollection();
    }

    public function getHeadBackgroundColor(): ?string
    {
        return $this->headBackgroundColor;
    }

    public function setHeadBackgroundColor(?string $headBackgroundColor): self
    {
        $this->headBackgroundColor = $headBackgroundColor;

        return $this;
    }

    public function getHeadColor(): ?string
    {
        return $this->headColor;
    }

    public function setHeadColor(?string $headColor): self
    {
        $this->headColor = $headColor;

        return $this;
    }

    /**
     * @return Collection|Col[]
     */
    public function getCols(): Collection
    {
        return $this->cols;
    }

    public function addCol(Col $col): self
    {
        if (!$this->cols->contains($col)) {
            $this->cols[] = $col;
            $col->setTable($this);
        }

        return $this;
    }

    public function removeCol(Col $col): self
    {
        if ($this->cols->contains($col)) {
            $this->cols->removeElement($col);
            // set the owning side to null (unless already changed)
            if ($col->getTable() === $this) {
                $col->setTable(null);
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
