<?php

namespace App\Entity\Translation;

use App\Repository\Translation\TranslationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Translation
 *
 * @ORM\Table(name="translation")
 * @ORM\Entity(repositoryClass=TranslationRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Translation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Translation\TranslationUnit", inversedBy="translations", fetch="EXTRA_LAZY")
     */
    private $unit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUnit(): ?TranslationUnit
    {
        return $this->unit;
    }

    public function setUnit(?TranslationUnit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }
}
