<?php

namespace App\Entity\Module\Table;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Module\Table\ColRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Col
 *
 * @ORM\Table(name="module_table_col")
 * @ORM\Entity(repositoryClass=ColRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Col extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'table';
    protected static $interface = [
        'name' => 'tablecol',
        'search' => true
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Table\Cell", mappedBy="col", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $cells;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Table\Table", inversedBy="cols", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $table;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_table_col_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="col_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Col constructor.
     */
    public function __construct()
    {
        $this->cells = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    /**
     * @return Collection|Cell[]
     */
    public function getCells(): Collection
    {
        return $this->cells;
    }

    public function addCell(Cell $cell): self
    {
        if (!$this->cells->contains($cell)) {
            $this->cells[] = $cell;
            $cell->setCol($this);
        }

        return $this;
    }

    public function removeCell(Cell $cell): self
    {
        if ($this->cells->contains($cell)) {
            $this->cells->removeElement($cell);
            // set the owning side to null (unless already changed)
            if ($cell->getCol() === $this) {
                $cell->setCol(null);
            }
        }

        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return Collection|i18n[]
     */
    public function getI18ns(): Collection
    {
        return $this->i18ns;
    }

    public function addI18n(i18n $i18n): self
    {
        if (!$this->i18ns->contains($i18n)) {
            $this->i18ns[] = $i18n;
        }

        return $this;
    }

    public function removeI18n(i18n $i18n): self
    {
        if ($this->i18ns->contains($i18n)) {
            $this->i18ns->removeElement($i18n);
        }

        return $this;
    }
}
