<?php

namespace App\Entity\Module\Making;

use App\Entity\BaseListing;
use App\Repository\Module\Making\ListingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Listing
 *
 * @ORM\Table(name="module_making_listing")
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
    protected static $interface = [
        'name' => 'makinglisting'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Making\Category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_making_listing_categories",
     *      joinColumns={@ORM\JoinColumn(name="listing_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Assert\Valid()
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
