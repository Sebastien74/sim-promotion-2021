<?php

namespace App\Entity\Translation;

use App\Entity\BaseInterface;
use App\Repository\Translation\TranslationUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TranslationUnit
 *
 * @ORM\Table(name="translation_unit")
 * @ORM\Entity(repositoryClass=TranslationUnitRepository::class)
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationUnit extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'domain';
    protected static $interface = [
        'name' => 'translationunit',
        'disabled_flash_bag' => true
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $keyName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Translation\Translation", mappedBy="unit", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     */
    private $translations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationDomain", inversedBy="units")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $domain;

    /**
     * TranslationUnit constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyName(): ?string
    {
        return $this->keyName;
    }

    public function setKeyName(string $keyName): self
    {
        $this->keyName = $keyName;

        return $this;
    }

    /**
     * @return Collection|Translation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(Translation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setUnit($this);
        }

        return $this;
    }

    public function removeTranslation(Translation $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
            // set the owning side to null (unless already changed)
            if ($translation->getUnit() === $this) {
                $translation->setUnit(null);
            }
        }

        return $this;
    }

    public function getDomain(): ?TranslationDomain
    {
        return $this->domain;
    }

    public function setDomain(?TranslationDomain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }
}
