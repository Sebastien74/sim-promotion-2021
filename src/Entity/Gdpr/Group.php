<?php

namespace App\Entity\Gdpr;

use App\Entity\BaseEntity;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Repository\Gdpr\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group
 *
 * @ORM\Table(name="gdpr_group")
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Group extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'gdprcategory';
    protected static $interface = [
        'name' => 'gdprgroup',
        'buttons' => [
            'admin_gdprcookie_index'
        ]
    ];
    protected static $labels = [
        "admin_gdprcookie_index" => "Cookies"
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $anonymize = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $service;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="gdpr_group_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC", "locale"="ASC"})
     * @ORM\JoinTable(name="gdpr_group_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gdpr\Cookie", mappedBy="gdprgroup", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $gdprcookies;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gdpr\Category", inversedBy="gdprgroups", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $gdprcategory;

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
        $this->gdprcookies = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getAnonymize(): ?bool
    {
        return $this->anonymize;
    }

    public function setAnonymize(bool $anonymize): self
    {
        $this->anonymize = $anonymize;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;

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

    /**
     * @return Collection|MediaRelation[]
     */
    public function getMediaRelations(): Collection
    {
        return $this->mediaRelations;
    }

    public function addMediaRelation(MediaRelation $mediaRelation): self
    {
        if (!$this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations[] = $mediaRelation;
        }

        return $this;
    }

    public function removeMediaRelation(MediaRelation $mediaRelation): self
    {
        if ($this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations->removeElement($mediaRelation);
        }

        return $this;
    }

    /**
     * @return Collection|Cookie[]
     */
    public function getGdprcookies(): Collection
    {
        return $this->gdprcookies;
    }

    public function addGdprcooky(Cookie $gdprcooky): self
    {
        if (!$this->gdprcookies->contains($gdprcooky)) {
            $this->gdprcookies[] = $gdprcooky;
            $gdprcooky->setGdprgroup($this);
        }

        return $this;
    }

    public function removeGdprcooky(Cookie $gdprcooky): self
    {
        if ($this->gdprcookies->contains($gdprcooky)) {
            $this->gdprcookies->removeElement($gdprcooky);
            // set the owning side to null (unless already changed)
            if ($gdprcooky->getGdprgroup() === $this) {
                $gdprcooky->setGdprgroup(null);
            }
        }

        return $this;
    }

    public function getGdprcategory(): ?Category
    {
        return $this->gdprcategory;
    }

    public function setGdprcategory(?Category $gdprcategory): self
    {
        $this->gdprcategory = $gdprcategory;

        return $this;
    }
}
