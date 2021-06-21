<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity
 *
 * @ORM\Table(name="core_entity")
 * @ORM\Entity(repositoryClass=EntityRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Entity extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'entity'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $className;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $entityId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $orderBy = 'position';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $orderSort = 'ASC';

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Assert\NotBlank()
     */
    private $columns = ['adminName'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $searchFields = ['adminName'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $searchFilters = [];

    /**
     * @ORM\Column(type="array")
     */
    private $showView = ['id', 'adminName', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy', 'position'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $exports = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $adminLimit = 15;

    /**
     * @ORM\Column(type="boolean")
     */
    private $uniqueLocale = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $saveArea = 'bottom';

    /**
     * @ORM\Column(type="boolean")
     */
    private $mediaMulti = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $asCard = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inFieldConfiguration = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(?string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getOrderSort(): ?string
    {
        return $this->orderSort;
    }

    public function setOrderSort(?string $orderSort): self
    {
        $this->orderSort = $orderSort;

        return $this;
    }

    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function setColumns(?array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function getSearchFields(): ?array
    {
        return $this->searchFields;
    }

    public function setSearchFields(?array $searchFields): self
    {
        $this->searchFields = $searchFields;

        return $this;
    }

    public function getSearchFilters(): ?array
    {
        return $this->searchFilters;
    }

    public function setSearchFilters(?array $searchFilters): self
    {
        $this->searchFilters = $searchFilters;

        return $this;
    }

    public function getShowView(): ?array
    {
        return $this->showView;
    }

    public function setShowView(array $showView): self
    {
        $this->showView = $showView;

        return $this;
    }

    public function getExports(): ?array
    {
        return $this->exports;
    }

    public function setExports(?array $exports): self
    {
        $this->exports = $exports;

        return $this;
    }

    public function getAdminLimit(): ?int
    {
        return $this->adminLimit;
    }

    public function setAdminLimit(?int $adminLimit): self
    {
        $this->adminLimit = $adminLimit;

        return $this;
    }

    public function getUniqueLocale(): ?bool
    {
        return $this->uniqueLocale;
    }

    public function setUniqueLocale(bool $uniqueLocale): self
    {
        $this->uniqueLocale = $uniqueLocale;

        return $this;
    }

    public function getSaveArea(): ?string
    {
        return $this->saveArea;
    }

    public function setSaveArea(string $saveArea): self
    {
        $this->saveArea = $saveArea;

        return $this;
    }

    public function getMediaMulti(): ?bool
    {
        return $this->mediaMulti;
    }

    public function setMediaMulti(bool $mediaMulti): self
    {
        $this->mediaMulti = $mediaMulti;

        return $this;
    }

    public function getAsCard(): ?bool
    {
        return $this->asCard;
    }

    public function setAsCard(bool $asCard): self
    {
        $this->asCard = $asCard;

        return $this;
    }

    public function getInFieldConfiguration(): ?bool
    {
        return $this->inFieldConfiguration;
    }

    public function setInFieldConfiguration(bool $inFieldConfiguration): self
    {
        $this->inFieldConfiguration = $inFieldConfiguration;

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
