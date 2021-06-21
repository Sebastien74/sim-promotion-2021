<?php

namespace App\Entity\Module\Tab;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Tab\TabRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tab
 *
 * @ORM\Table(name="module_tab")
 * @ORM\Entity(repositoryClass=TabRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Tab extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'tab',
        'buttons' => [
            'admin_tabcontent_tree'
        ]
    ];
    protected static $labels = [
        "admin_tabcontent_tree" => "Contenus"
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Tab\Content", mappedBy="tab", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $contents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Tab constructor.
     */
    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    /**
     * @return Collection|Content[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setTab($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getTab() === $this) {
                $content->setTab(null);
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
