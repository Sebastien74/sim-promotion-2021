<?php

namespace App\Entity\Module\Making;

use App\Entity\BaseTeaser;
use App\Entity\Translation\i18n;
use App\Repository\Module\Making\TeaserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Teaser
 *
 * @ORM\Table(name="module_making_teaser")
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
        'name' => 'makingteaser'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Making\Category", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="module_making_teasers_categories",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     * @Assert\Valid()
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_making_teaser_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="teaser_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
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
}
