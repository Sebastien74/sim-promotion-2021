<?php

namespace App\Entity\Module\Portfolio;

use App\Entity\BaseListing;
use App\Entity\Layout\Layout;
use App\Repository\Module\Portfolio\ListingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Listing
 *
 * @ORM\Table(name="module_portfolio_listing")
 * @ORM\Entity(repositoryClass=ListingRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Listing extends BaseListing
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'portfoliolisting',
        'resize' => true
    ];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Portfolio\Category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_portfolio_listing_categories",
     *      joinColumns={@ORM\JoinColumn(name="listing_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Assert\Valid()
     */
    private $categories;

    /**
     * Listing constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
