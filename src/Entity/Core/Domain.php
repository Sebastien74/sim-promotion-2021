<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\DomainRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Configuration
 *
 * @ORM\Table(name="core_domain")
 * @ORM\Entity(repositoryClass=DomainRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity("name")
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Domain extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'domain'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasDefault;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="domains", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $configuration;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
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

    public function getHasDefault(): ?bool
    {
        return $this->hasDefault;
    }

    public function setHasDefault(bool $hasDefault): self
    {
        $this->hasDefault = $hasDefault;

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
