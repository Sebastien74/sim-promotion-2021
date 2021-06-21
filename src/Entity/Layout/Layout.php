<?php

namespace App\Entity\Layout;

use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Product;
use App\Entity\Module\Event\Event;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Making\Making;
use App\Entity\Module\Newscast as NewscastEntity;
use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Layout\LayoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Layout
 *
 * @ORM\Table(name="layout")
 * @ORM\Entity(repositoryClass=LayoutRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Layout extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'layout'
    ];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Page", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $page;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\Form", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $form;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Newscast\Newscast", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $newscast;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Newscast\Category", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $newscastcategory;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Event\Event", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Event\Category", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $eventcategory;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Catalog\Catalog", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $catalog;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Catalog\Product", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $catalogproduct;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Making\Making", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $making;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Making\Category", mappedBy="layout", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $makingcategory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\Zone", mappedBy="layout", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $zones;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\LayoutConfiguration", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $configuration;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Layout constructor.
     */
    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    /**
     * Get parent entity
     *
     * @param EntityManagerInterface $entityManager
     * @return object
     */
    public function getParent(EntityManagerInterface $entityManager)
    {
        $metasData = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metasData as $metaData) {
            $classname = $metaData->getName();
            $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;
            if (method_exists($baseEntity, 'getLayout')) {
                $parent = $entityManager->getRepository($classname)->findOneBy(['layout' => $this]);
                if($parent && method_exists($parent, 'setUpdatedAt')) {
                    return $parent;
                }
            }
        }
    }

    /**
     * Set parent entity
     *
     * @param EntityManagerInterface $entityManager
     */
    public function setParent(EntityManagerInterface $entityManager)
    {
        $parent = $this->getParent($entityManager);
        if (is_object($parent) && method_exists($parent, 'getLayout')) {
            $parent->setUpdatedAt(new \DateTime('now'));
            $entityManager->persist($parent);
        }
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = $page === null ? null : $this;
        if ($newLayout !== $page->getLayout()) {
            $page->setLayout($newLayout);
        }

        return $this;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): self
    {
        $this->form = $form;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = $form === null ? null : $this;
        if ($newLayout !== $form->getLayout()) {
            $form->setLayout($newLayout);
        }

        return $this;
    }

    public function getNewscast(): ?NewscastEntity\Newscast
    {
        return $this->newscast;
    }

    public function setNewscast(?NewscastEntity\Newscast $newscast): self
    {
        $this->newscast = $newscast;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = $newscast === null ? null : $this;
        if ($newLayout !== $newscast->getLayout()) {
            $newscast->setLayout($newLayout);
        }

        return $this;
    }

    public function getNewscastcategory(): ?NewscastEntity\Category
    {
        return $this->newscastcategory;
    }

    public function setNewscastcategory(?NewscastEntity\Category $newscastcategory): self
    {
        $this->newscastcategory = $newscastcategory;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = $newscastcategory === null ? null : $this;
        if ($newLayout !== $newscastcategory->getLayout()) {
            $newscastcategory->setLayout($newLayout);
        }

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        // unset the owning side of the relation if necessary
        if ($event === null && $this->event !== null) {
            $this->event->setLayout(null);
        }

        // set the owning side of the relation if necessary
        if ($event !== null && $event->getLayout() !== $this) {
            $event->setLayout($this);
        }

        $this->event = $event;

        return $this;
    }

    public function getEventcategory(): ?\App\Entity\Module\Event\Category
    {
        return $this->eventcategory;
    }

    public function setEventcategory(?\App\Entity\Module\Event\Category $eventcategory): self
    {
        // unset the owning side of the relation if necessary
        if ($eventcategory === null && $this->eventcategory !== null) {
            $this->eventcategory->setLayout(null);
        }

        // set the owning side of the relation if necessary
        if ($eventcategory !== null && $eventcategory->getLayout() !== $this) {
            $eventcategory->setLayout($this);
        }

        $this->eventcategory = $eventcategory;

        return $this;
    }

    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    public function setCatalog(?Catalog $catalog): self
    {
        $this->catalog = $catalog;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = null === $catalog ? null : $this;
        if ($catalog->getLayout() !== $newLayout) {
            $catalog->setLayout($newLayout);
        }

        return $this;
    }

    public function getCatalogproduct(): ?Product
    {
        return $this->catalogproduct;
    }

    public function setCatalogproduct(?Product $catalogproduct): self
    {
        $this->catalogproduct = $catalogproduct;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = null === $catalogproduct ? null : $this;
        if ($catalogproduct->getLayout() !== $newLayout) {
            $catalogproduct->setLayout($newLayout);
        }

        return $this;
    }

    public function getMaking(): ?Making
    {
        return $this->making;
    }

    public function setMaking(?Making $making): self
    {
        $this->making = $making;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = null === $making ? null : $this;
        if ($making->getLayout() !== $newLayout) {
            $making->setLayout($newLayout);
        }

        return $this;
    }

    public function getMakingcategory(): ?\App\Entity\Module\Making\Category
    {
        return $this->makingcategory;
    }

    public function setMakingcategory(?\App\Entity\Module\Making\Category $makingcategory): self
    {
        $this->makingcategory = $makingcategory;

        // set (or unset) the owning side of the relation if necessary
        $newLayout = null === $makingcategory ? null : $this;
        if ($makingcategory->getLayout() !== $newLayout) {
            $makingcategory->setLayout($newLayout);
        }

        return $this;
    }

    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): self
    {
        if (!$this->zones->contains($zone)) {
            $this->zones[] = $zone;
            $zone->setLayout($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): self
    {
        if ($this->zones->contains($zone)) {
            $this->zones->removeElement($zone);
            // set the owning side to null (unless already changed)
            if ($zone->getLayout() === $this) {
                $zone->setLayout(null);
            }
        }

        return $this;
    }

    public function getConfiguration(): ?LayoutConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Website $configuration): self
    {
        $this->configuration = $configuration;

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
}
