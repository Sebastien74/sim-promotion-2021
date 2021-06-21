<?php

namespace App\Entity\Translation;

use App\Entity\BaseInterface;
use App\Repository\Translation\TranslationDomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TranslationDomain
 *
 * @ORM\Table(name="translation_domain")
 * @ORM\Entity(repositoryClass=TranslationDomainRepository::class)
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationDomain extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'translationdomain'
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
    private $adminName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $extract = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forTranslator = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\TranslationUnit", mappedBy="domain", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $units;

    /**
     * TranslationDomain constructor.
     */
    public function __construct()
    {
        $this->units = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdminName(): ?string
    {
        return $this->adminName;
    }

    public function setAdminName(?string $adminName): self
    {
        $this->adminName = $adminName;

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

    public function getForTranslator(): ?bool
    {
        return $this->forTranslator;
    }

    public function setForTranslator(bool $forTranslator): self
    {
        $this->forTranslator = $forTranslator;

        return $this;
    }

    public function getExtract(): ?bool
    {
        return $this->extract;
    }

    public function setExtract(bool $extract): self
    {
        $this->extract = $extract;

        return $this;
    }

    /**
     * @return Collection|TranslationUnit[]
     */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function addUnit(TranslationUnit $unit): self
    {
        if (!$this->units->contains($unit)) {
            $this->units[] = $unit;
            $unit->setDomain($this);
        }

        return $this;
    }

    public function removeUnit(TranslationUnit $unit): self
    {
        if ($this->units->contains($unit)) {
            $this->units->removeElement($unit);
            // set the owning side to null (unless already changed)
            if ($unit->getDomain() === $this) {
                $unit->setDomain(null);
            }
        }

        return $this;
    }
}
