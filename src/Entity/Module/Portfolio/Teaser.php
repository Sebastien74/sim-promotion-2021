<?php

namespace App\Entity\Module\Portfolio;

use App\Entity\BaseTeaser;
use App\Repository\Module\Portfolio\TeaserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Teaser
 *
 * @ORM\Table(name="module_portfolio_teaser")
 * @ORM\Entity(repositoryClass=TeaserRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Teaser extends BaseTeaser
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'portfolioteaser',
        'module' => 'portfolio'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Portfolio\Category", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_portfolio_teaser_categories",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $categories;

    /**
     * Teaser constructor.
     */
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
        $this->categories->removeElement($category);

        return $this;
    }
}
