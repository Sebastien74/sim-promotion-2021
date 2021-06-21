<?php

namespace App\Entity\Gdpr;

use App\Entity\BaseEntity;
use App\Entity\Core\Configuration;
use App\Repository\Gdpr\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="gdpr_category")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Category extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'configuration';
    protected static $interface = [
        'name' => 'gdprcategory',
        'buttons' => [
            'admin_gdprgroup_index'
        ]
    ];
    protected static $labels = [
        "admin_gdprgroup_index" => "Groupes"
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Configuration", inversedBy="gdprcategories", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $configuration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gdpr\Group", mappedBy="gdprcategory", fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $gdprgroups;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->gdprgroups = new ArrayCollection();
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGdprgroups(): Collection
    {
        return $this->gdprgroups;
    }

    public function addGdprgroup(Group $gdprgroup): self
    {
        if (!$this->gdprgroups->contains($gdprgroup)) {
            $this->gdprgroups[] = $gdprgroup;
            $gdprgroup->setGdprcategory($this);
        }

        return $this;
    }

    public function removeGdprgroup(Group $gdprgroup): self
    {
        if ($this->gdprgroups->contains($gdprgroup)) {
            $this->gdprgroups->removeElement($gdprgroup);
            // set the owning side to null (unless already changed)
            if ($gdprgroup->getGdprcategory() === $this) {
                $gdprgroup->setGdprcategory(null);
            }
        }

        return $this;
    }
}
