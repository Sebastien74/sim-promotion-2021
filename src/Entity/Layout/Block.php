<?php

namespace App\Entity\Layout;

use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Layout\BlockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Block
 *
 * @ORM\Table(name="layout_block")
 * @ORM\Entity(repositoryClass=BlockRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Block extends BaseConfiguration
{
	/**
	 * Configurations
	 */
	protected static $masterField = 'col';
	protected static $interface = [
		'name' => 'block',
		'search' => true
	];

	/**
	 * @ORM\Column(type="integer")
	 */
	private $size = 12;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $mobileSize;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $tabletSize;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $height;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $timer;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $template = 'default';

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $color;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $backgroundFullSize = true;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $backgroundColorType;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $icon;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $iconSize;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $iconPosition = 'left';

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $script;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $autoplay = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $controls = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $soundControls = false;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\Layout\FieldConfiguration", mappedBy="block", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
	 */
	private $fieldConfiguration;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Layout\ActionI18n", mappedBy="block", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @ORM\OrderBy({"locale"="ASC"})
	 * @Assert\Valid()
	 */
	private $actionI18ns;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Col", inversedBy="blocks", cascade={"persist"}, fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $col;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Layout\BlockType", fetch="EXTRA_LAZY")
	 */
	private $blockType;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Action", fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
	 */
	private $action;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
	 * @ORM\OrderBy({"locale"="ASC"})
	 * @ORM\JoinTable(name="layout_block_i18ns",
	 *      joinColumns={@ORM\JoinColumn(name="layout_block_id", referencedColumnName="id", onDelete="cascade")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
	 * )
	 */
	private $i18ns;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
	 * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
	 * @ORM\JoinTable(name="layout_block_media_relations",
	 *      joinColumns={@ORM\JoinColumn(name="block_id", referencedColumnName="id", onDelete="cascade")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
	 * )
	 * @Assert\Valid()
	 */
	private $mediaRelations;

	/**
	 * Block constructor.
	 */
	public function __construct()
	{
		$this->i18ns = new ArrayCollection();
		$this->mediaRelations = new ArrayCollection();
		$this->actionI18ns = new ArrayCollection();
	}

	public function getSize(): ?int
	{
		return $this->size;
	}

	public function setSize(int $size): self
	{
		$this->size = $size;

		return $this;
	}

	public function getMobileSize(): ?int
	{
		return $this->mobileSize;
	}

	public function setMobileSize(?int $mobileSize): self
	{
		$this->mobileSize = $mobileSize;

		return $this;
	}

	public function getTabletSize(): ?int
	{
		return $this->tabletSize;
	}

	public function setTabletSize(?int $tabletSize): self
	{
		$this->tabletSize = $tabletSize;

		return $this;
	}

	public function getHeight(): ?int
	{
		return $this->height;
	}

	public function setHeight(?int $height): self
	{
		$this->height = $height;

		return $this;
	}

	public function getTimer(): ?int
	{
		return $this->timer;
	}

	public function setTimer(?int $timer): self
	{
		$this->timer = $timer;

		return $this;
	}

	public function getTemplate(): ?string
	{
		return $this->template;
	}

	public function setTemplate(string $template): self
	{
		$this->template = $template;

		return $this;
	}

	public function getColor(): ?string
	{
		return $this->color;
	}

	public function setColor(?string $color): self
	{
		$this->color = $color;

		return $this;
	}

	public function getBackgroundFullSize(): ?bool
	{
		return $this->backgroundFullSize;
	}

	public function setBackgroundFullSize(bool $backgroundFullSize): self
	{
		$this->backgroundFullSize = $backgroundFullSize;

		return $this;
	}

	public function getBackgroundColorType(): ?string
	{
		return $this->backgroundColorType;
	}

	public function setBackgroundColorType(?string $backgroundColorType): self
	{
		$this->backgroundColorType = $backgroundColorType;

		return $this;
	}

	public function getIcon(): ?string
	{
		return $this->icon;
	}

	public function setIcon(?string $icon): self
	{
		$this->icon = $icon;

		return $this;
	}

	public function getIconSize(): ?string
	{
		return $this->iconSize;
	}

	public function setIconSize(?string $iconSize): self
	{
		$this->iconSize = $iconSize;

		return $this;
	}

	public function getIconPosition(): ?string
	{
		return $this->iconPosition;
	}

	public function setIconPosition(?string $iconPosition): self
	{
		$this->iconPosition = $iconPosition;

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

	public function getAutoplay(): ?bool
	{
		return $this->autoplay;
	}

	public function setAutoplay(bool $autoplay): self
	{
		$this->autoplay = $autoplay;

		return $this;
	}

	public function getControls(): ?bool
	{
		return $this->controls;
	}

	public function setControls(bool $controls): self
	{
		$this->controls = $controls;

		return $this;
	}

	public function getSoundControls(): ?bool
	{
		return $this->soundControls;
	}

	public function setSoundControls(bool $soundControls): self
	{
		$this->soundControls = $soundControls;

		return $this;
	}

	public function getFieldConfiguration(): ?FieldConfiguration
	{
		return $this->fieldConfiguration;
	}

	public function setFieldConfiguration(?FieldConfiguration $fieldConfiguration): self
	{
		$this->fieldConfiguration = $fieldConfiguration;

		// set (or unset) the owning side of the relation if necessary
		$newBlock = null === $fieldConfiguration ? null : $this;
		if ($fieldConfiguration->getBlock() !== $newBlock) {
			$fieldConfiguration->setBlock($newBlock);
		}

		return $this;
	}

	/**
	 * @return Collection|ActionI18n[]
	 */
	public function getActionI18ns(): Collection
	{
		return $this->actionI18ns;
	}

	public function addActionI18n(ActionI18n $actionI18n): self
	{
		if (!$this->actionI18ns->contains($actionI18n)) {
			$this->actionI18ns[] = $actionI18n;
			$actionI18n->setBlock($this);
		}

		return $this;
	}

	public function removeActionI18n(ActionI18n $actionI18n): self
	{
		if ($this->actionI18ns->contains($actionI18n)) {
			$this->actionI18ns->removeElement($actionI18n);
			// set the owning side to null (unless already changed)
			if ($actionI18n->getBlock() === $this) {
				$actionI18n->setBlock(null);
			}
		}

		return $this;
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

	public function getBlockType(): ?BlockType
	{
		return $this->blockType;
	}

	public function setBlockType(?BlockType $blockType): self
	{
		$this->blockType = $blockType;

		return $this;
	}

	public function getAction(): ?Action
	{
		return $this->action;
	}

	public function setAction(?Action $action): self
	{
		$this->action = $action;

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

	/**
	 * @return Collection|MediaRelation[]
	 */
	public function getMediaRelations(): Collection
	{
		return $this->mediaRelations;
	}

	public function addMediaRelation(MediaRelation $mediaRelation): self
	{
		if (!$this->mediaRelations->contains($mediaRelation)) {
			$this->mediaRelations[] = $mediaRelation;
		}

		return $this;
	}

	public function removeMediaRelation(MediaRelation $mediaRelation): self
	{
		if ($this->mediaRelations->contains($mediaRelation)) {
			$this->mediaRelations->removeElement($mediaRelation);
		}

		return $this;
	}
}