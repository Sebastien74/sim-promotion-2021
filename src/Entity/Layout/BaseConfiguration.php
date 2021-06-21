<?php

namespace App\Entity\Layout;

use App\Entity\BaseEntity;
use App\Entity\Core\Transition;
use Doctrine\ORM\Mapping as ORM;

/**
 * BaseConfiguration
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseConfiguration extends BaseEntity
{
	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $fullSize = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $standardizeElements = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $hide = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $verticalAlign = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $endAlign = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $reverse = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $hideMobile = false;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $backgroundColor;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $alignment;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $elementsAlignment;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $mobilePosition;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $tabletPosition;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $marginTop;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $marginRight;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $marginBottom;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $marginLeft;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $paddingTop;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $paddingRight;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $paddingBottom;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	protected $paddingLeft;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $duration;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $delay;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Core\Transition", cascade={"persist"}, fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
	 */
	protected $transition;

	public function getFullSize(): ?bool
	{
		return $this->fullSize;
	}

	public function setFullSize(bool $fullSize): self
	{
		$this->fullSize = $fullSize;

		return $this;
	}

	public function getStandardizeElements(): ?bool
	{
		return $this->standardizeElements;
	}

	public function setStandardizeElements(bool $standardizeElements): self
	{
		$this->standardizeElements = $standardizeElements;

		return $this;
	}

	public function getHide(): ?bool
	{
		return $this->hide;
	}

	public function setHide(bool $hide): self
	{
		$this->hide = $hide;

		return $this;
	}

	public function getVerticalAlign(): ?bool
	{
		return $this->verticalAlign;
	}

	public function setVerticalAlign(bool $verticalAlign): self
	{
		$this->verticalAlign = $verticalAlign;

		return $this;
	}

	public function getEndAlign(): ?bool
	{
		return $this->endAlign;
	}

	public function setEndAlign(bool $endAlign): self
	{
		$this->endAlign = $endAlign;

		return $this;
	}

	public function getReverse(): ?bool
	{
		return $this->reverse;
	}

	public function setReverse(bool $reverse): self
	{
		$this->reverse = $reverse;

		return $this;
	}

	public function getHideMobile(): ?bool
	{
		return $this->hideMobile;
	}

	public function setHideMobile(bool $hideMobile): self
	{
		$this->hideMobile = $hideMobile;

		return $this;
	}

	public function getBackgroundColor(): ?string
	{
		return $this->backgroundColor;
	}

	public function setBackgroundColor(?string $backgroundColor): self
	{
		$this->backgroundColor = $backgroundColor;

		return $this;
	}

	public function getAlignment(): ?string
	{
		return $this->alignment;
	}

	public function setAlignment(?string $alignment): self
	{
		$this->alignment = $alignment;

		return $this;
	}

	public function getElementsAlignment(): ?string
	{
		return $this->elementsAlignment;
	}

	public function setElementsAlignment(?string $elementsAlignment): self
	{
		$this->elementsAlignment = $elementsAlignment;

		return $this;
	}

	public function getMobilePosition(): ?int
	{
		return $this->mobilePosition;
	}

	public function setMobilePosition(?int $mobilePosition): self
	{
		$this->mobilePosition = $mobilePosition;

		return $this;
	}

	public function getTabletPosition(): ?int
	{
		return $this->tabletPosition;
	}

	public function setTabletPosition(?int $tabletPosition): self
	{
		$this->tabletPosition = $tabletPosition;

		return $this;
	}

	public function getMarginTop(): ?string
	{
		return $this->marginTop;
	}

	public function setMarginTop(?string $marginTop): self
	{
		$this->marginTop = $marginTop;

		return $this;
	}

	public function getMarginRight(): ?string
	{
		return $this->marginRight;
	}

	public function setMarginRight(?string $marginRight): self
	{
		$this->marginRight = $marginRight;

		return $this;
	}

	public function getMarginBottom(): ?string
	{
		return $this->marginBottom;
	}

	public function setMarginBottom(?string $marginBottom): self
	{
		$this->marginBottom = $marginBottom;

		return $this;
	}

	public function getMarginLeft(): ?string
	{
		return $this->marginLeft;
	}

	public function setMarginLeft(?string $marginLeft): self
	{
		$this->marginLeft = $marginLeft;

		return $this;
	}

	public function getPaddingTop(): ?string
	{
		return $this->paddingTop;
	}

	public function setPaddingTop(?string $paddingTop): self
	{
		$this->paddingTop = $paddingTop;

		return $this;
	}

	public function getPaddingRight(): ?string
	{
		return $this->paddingRight;
	}

	public function setPaddingRight(?string $paddingRight): self
	{
		$this->paddingRight = $paddingRight;

		return $this;
	}

	public function getPaddingBottom(): ?string
	{
		return $this->paddingBottom;
	}

	public function setPaddingBottom(?string $paddingBottom): self
	{
		$this->paddingBottom = $paddingBottom;

		return $this;
	}

	public function getPaddingLeft(): ?string
	{
		return $this->paddingLeft;
	}

	public function setPaddingLeft(?string $paddingLeft): self
	{
		$this->paddingLeft = $paddingLeft;

		return $this;
	}

	public function getDuration(): ?string
	{
		return $this->duration;
	}

	public function setDuration(?string $duration): self
	{
		$this->duration = $duration;

		return $this;
	}

	public function getDelay(): ?string
	{
		return $this->delay;
	}

	public function setDelay(?string $delay): self
	{
		$this->delay = $delay;

		return $this;
	}

	public function getTransition(): ?Transition
	{
		return $this->transition;
	}

	public function setTransition(?Transition $transition): self
	{
		$this->transition = $transition;

		return $this;
	}
}