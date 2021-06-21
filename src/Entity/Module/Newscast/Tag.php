<?php

namespace App\Entity\Module\Newscast;

use App\Entity\BaseEntity;
use App\Repository\Module\Newscast\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table(name="module_newscast_tag")
 * @ORM\Entity(repositoryClass=TagRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Tag extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'newscasttag'
    ];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Module\Newscast\Newscast", mappedBy="tags", fetch="EXTRA_LAZY")
     */
    private $newscasts;

    /**
     * Tag constructor.
     */
    public function __construct()
    {
        $this->newscasts = new ArrayCollection();
    }

    /**
     * @return Collection|Newscast[]
     */
    public function getNewscasts(): Collection
    {
        return $this->newscasts;
    }

    public function addNewscast(Newscast $newscast): self
    {
        if (!$this->newscasts->contains($newscast)) {
            $this->newscasts[] = $newscast;
            $newscast->addTag($this);
        }

        return $this;
    }

    public function removeNewscast(Newscast $newscast): self
    {
        if ($this->newscasts->contains($newscast)) {
            $this->newscasts->removeElement($newscast);
            $newscast->removeTag($this);
        }

        return $this;
    }
}
