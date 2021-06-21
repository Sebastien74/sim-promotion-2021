<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Security\UserCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserCategory
 *
 * @ORM\Table(name="security_user_category")
 * @ORM\Entity(repositoryClass=UserCategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserCategory extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'securityusercategory'
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Security\UserFront", mappedBy="category", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $userFronts;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="security_user_category_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * UserCategory constructor.
     */
    public function __construct()
    {
        $this->userFronts = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    /**
     * @return Collection|UserFront[]
     */
    public function getUserFronts(): Collection
    {
        return $this->userFronts;
    }

    public function addUserFront(UserFront $userFront): self
    {
        if (!$this->userFronts->contains($userFront)) {
            $this->userFronts[] = $userFront;
            $userFront->setCategory($this);
        }

        return $this;
    }

    public function removeUserFront(UserFront $userFront): self
    {
        if ($this->userFronts->contains($userFront)) {
            $this->userFronts->removeElement($userFront);
            // set the owning side to null (unless already changed)
            if ($userFront->getCategory() === $this) {
                $userFront->setCategory(null);
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
        if ($this->i18ns->contains($i18n)) {
            $this->i18ns->removeElement($i18n);
        }

        return $this;
    }
}
