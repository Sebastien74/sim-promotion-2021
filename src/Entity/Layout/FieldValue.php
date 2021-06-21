<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Layout\FieldValueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * FieldValue
 *
 * @ORM\Table(name="layout_field_configuration_value")
 * @ORM\Entity(repositoryClass=FieldValueRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldValue extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'configuration';
    protected static $interface = [
        'name' => 'fieldvalue'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="layout_field_configuration_value_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\FieldConfiguration", inversedBy="fieldValues", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    /**
     * FieldValue constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
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

    public function getConfiguration(): ?FieldConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?FieldConfiguration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
