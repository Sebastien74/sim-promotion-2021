<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Seo\SeoConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SeoConfiguration
 *
 * @ORM\Table(name="seo_configuration")
 * @ORM\Entity(repositoryClass=SeoConfigurationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoConfiguration extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'seoconfiguration'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $microData = true;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $disabledIps = ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Website", inversedBy="seoConfiguration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\Valid()
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="seo_configuration_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * SeoConfiguration constructor.
     */
    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMicroData(): ?bool
    {
        return $this->microData;
    }

    public function setMicroData(bool $microData): self
    {
        $this->microData = $microData;

        return $this;
    }

    public function getDisabledIps(): ?array
    {
        return $this->disabledIps;
    }

    public function setDisabledIps(?array $disabledIps): self
    {
        $this->disabledIps = $disabledIps;

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(Website $website): self
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
