<?php

namespace App\Entity\Layout;

use App\Repository\Layout\FieldConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * FieldConfiguration
 *
 * @ORM\Table(name="layout_field_configuration")
 * @ORM\Entity(repositoryClass=FieldConfigurationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldConfiguration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $constraints = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $preferredChoices = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $required = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $multiple = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $expanded = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $picker = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inline = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $regex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $min;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $max;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxFileSize;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $filesTypes = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $buttonType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $script;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $className;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Block", inversedBy="fieldConfiguration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $block;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\FieldValue", mappedBy="configuration", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $fieldValues;

    /**
     * FieldConfiguration constructor.
     */
    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function setConstraints(?array $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function getPreferredChoices(): ?array
    {
        return $this->preferredChoices;
    }

    public function setPreferredChoices(?array $preferredChoices): self
    {
        $this->preferredChoices = $preferredChoices;

        return $this;
    }

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getMultiple(): ?bool
    {
        return $this->multiple;
    }

    public function setMultiple(?bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getExpanded(): ?bool
    {
        return $this->expanded;
    }

    public function setExpanded(?bool $expanded): self
    {
        $this->expanded = $expanded;

        return $this;
    }

    public function getPicker(): ?bool
    {
        return $this->picker;
    }

    public function setPicker(?bool $picker): self
    {
        $this->picker = $picker;

        return $this;
    }

    public function getInline(): ?bool
    {
        return $this->inline;
    }

    public function setInline(?bool $inline): self
    {
        $this->inline = $inline;

        return $this;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(?string $regex): self
    {
        $this->regex = $regex;

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getMaxFileSize(): ?int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(?int $maxFileSize): self
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function getFilesTypes(): ?array
    {
        return $this->filesTypes;
    }

    public function setFilesTypes(?array $filesTypes): self
    {
        $this->filesTypes = $filesTypes;

        return $this;
    }

    public function getButtonType(): ?string
    {
        return $this->buttonType;
    }

    public function setButtonType(?string $buttonType): self
    {
        $this->buttonType = $buttonType;

        return $this;
    }

    public function getScript(): ?string
    {
        return $this->script;
    }

    public function setScript(?string $script): self
    {
        $this->script = $script;

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function setBlock(?Block $block): self
    {
        $this->block = $block;

        return $this;
    }

    /**
     * @return Collection|FieldValue[]
     */
    public function getFieldValues(): Collection
    {
        return $this->fieldValues;
    }

    public function addFieldValue(FieldValue $fieldValue): self
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues[] = $fieldValue;
            $fieldValue->setConfiguration($this);
        }

        return $this;
    }

    public function removeFieldValue(FieldValue $fieldValue): self
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
            // set the owning side to null (unless already changed)
            if ($fieldValue->getConfiguration() === $this) {
                $fieldValue->setConfiguration(null);
            }
        }

        return $this;
    }
}
