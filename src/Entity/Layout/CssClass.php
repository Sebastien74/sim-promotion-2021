<?php

namespace App\Entity\Layout;

use App\Entity\BaseInterface;
use App\Entity\Core\Configuration;
use App\Repository\Layout\CssClassRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CssClass
 *
 * @ORM\Table(name="layout_css_class")
 * @ORM\Entity(repositoryClass=CssClassRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CssClass extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'cssclass'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="cssClasses", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}