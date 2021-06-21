<?php

namespace App\Entity\Module\Table;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Module\Table\CellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cell
 *
 * @ORM\Table(name="module_table_cell")
 * @ORM\Entity(repositoryClass=CellRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Cell extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'col';
    protected static $interface = [
        'name' => 'tablecell',
        'search' => true
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Table\Col", inversedBy="cells", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $col;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_table_cell_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="cell_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Cell constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
    }

    public function getCol(): ?Col
    {
        return $this->col;
    }

    public function setCol(?Col $col): self
    {
        $this->col = $col;

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
