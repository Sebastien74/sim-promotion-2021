<?php

namespace App\Entity\Module\Contact;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Module\Contact\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Contact
 *
 * @ORM\Table(name="module_contact")
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Contact extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'contact'
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_contact_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    public function __construct()
    {
        $this->i18ns = new ArrayCollection();
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
