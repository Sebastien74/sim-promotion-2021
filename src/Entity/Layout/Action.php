<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Core\Module;
use App\Repository\Layout\ActionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="layout_action")
 * @ORM\Entity(repositoryClass=ActionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Action extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'action'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $controller;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entity;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCard = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $iconClass;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dropdown = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Module", inversedBy="actions", fetch="EXTRA_LAZY")
     */
    private $module;

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getIsCard(): ?bool
    {
        return $this->isCard;
    }

    public function setIsCard(bool $isCard): self
    {
        $this->isCard = $isCard;

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

    public function getDropdown(): ?bool
    {
        return $this->dropdown;
    }

    public function setDropdown(bool $dropdown): self
    {
        $this->dropdown = $dropdown;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }
}
