<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Entity\Layout\Action;
use App\Entity\Translation\i18n;
use App\Repository\Core\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Module
 *
 * @ORM\Table(name="core_module")
 * @ORM\Entity(repositoryClass=ModuleRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Module extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'module'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $iconClass;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inAdvert = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\Action", mappedBy="module", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $actions;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="core_module_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="module_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    public function setIconClass(string $iconClass): self
    {
        $this->iconClass = $iconClass;

        return $this;
    }

    public function getInAdvert(): ?bool
    {
        return $this->inAdvert;
    }

    public function setInAdvert(bool $inAdvert): self
    {
        $this->inAdvert = $inAdvert;

        return $this;
    }

    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setModule($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getModule() === $this) {
                $action->setModule(null);
            }
        }

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
