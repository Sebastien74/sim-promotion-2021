<?php

namespace App\Entity\Module\Event;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Module\Event\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="module_event_category")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Category extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'eventcategory',
        'resize' => true
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefault = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mainMediaInHeader = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hideDate = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayCategory = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formatDate = "dd/MM";

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $itemsPerPage = 12;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderBy = 'publicationStart-desc';

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="eventcategory", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Event\Event", mappedBy="category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"publicationStart"="DESC"})
     * @Assert\Valid()
     */
    private $events;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_event_category_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="module_event_category_relations",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getMainMediaInHeader(): ?bool
    {
        return $this->mainMediaInHeader;
    }

    public function setMainMediaInHeader(bool $mainMediaInHeader): self
    {
        $this->mainMediaInHeader = $mainMediaInHeader;

        return $this;
    }

    public function getHideDate(): ?bool
    {
        return $this->hideDate;
    }

    public function setHideDate(bool $hideDate): self
    {
        $this->hideDate = $hideDate;

        return $this;
    }

    public function getDisplayCategory(): ?bool
    {
        return $this->displayCategory;
    }

    public function setDisplayCategory(bool $displayCategory): self
    {
        $this->displayCategory = $displayCategory;

        return $this;
    }

    public function getFormatDate(): ?string
    {
        return $this->formatDate;
    }

    public function setFormatDate(?string $formatDate): self
    {
        $this->formatDate = $formatDate;

        return $this;
    }

    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(?int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setCategory($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCategory() === $this) {
                $event->setCategory(null);
            }
        }

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

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
        $this->i18ns->removeElement($i18n);

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
        $this->mediaRelations->removeElement($mediaRelation);

        return $this;
    }
}
