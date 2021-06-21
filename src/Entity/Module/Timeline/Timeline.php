<?php

namespace App\Entity\Module\Timeline;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Timeline\TimelineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Timeline
 *
 * @ORM\Table(name="module_timeline")
 * @ORM\Entity(repositoryClass=TimelineRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Timeline extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'timeline',
        'buttons' => [
            'admin_timelinestep_index'
        ]
    ];
    protected static $labels = [
        "admin_timelinestep_index" => "Étapes"
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Timeline\Step", mappedBy="timeline", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $steps;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    /**
     * @return Collection|Step[]
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
            $step->setTimeline($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
            // set the owning side to null (unless already changed)
            if ($step->getTimeline() === $this) {
                $step->setTimeline(null);
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
}
