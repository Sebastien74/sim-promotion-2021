<?php

namespace App\Entity\Module\Search;

use App\Entity\Module\Newscast\Newscast;
use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Repository\Module\Search\SearchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Search
 *
 * @ORM\Table(name="module_search")
 * @ORM\Entity(repositoryClass=SearchRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Search extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'search'
    ];

    /**
     * @ORM\Column(type="array")
     */
    private $entities = [Newscast::class, Block::class];

    /**
     * @ORM\Column(type="boolean")
     */
    private $filterGroup = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $itemsPerPage = 9;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderBy = 'date-desc';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $searchType = 'sentence';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Page", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="results_page_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $resultsPage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getEntities(): ?array
    {
        return $this->entities;
    }

    public function setEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    public function getFilterGroup(): ?bool
    {
        return $this->filterGroup;
    }

    public function setFilterGroup(bool $filterGroup): self
    {
        $this->filterGroup = $filterGroup;

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

    public function getSearchType(): ?string
    {
        return $this->searchType;
    }

    public function setSearchType(string $searchType): self
    {
        $this->searchType = $searchType;

        return $this;
    }

    public function getResultsPage(): ?Page
    {
        return $this->resultsPage;
    }

    public function setResultsPage(?Page $resultsPage): self
    {
        $this->resultsPage = $resultsPage;

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