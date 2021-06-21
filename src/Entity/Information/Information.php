<?php

namespace App\Entity\Information;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Information\InformationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Information
 *
 * @ORM\Table(name="information")
 * @ORM\Entity(repositoryClass=InformationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Information extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'information'
    ];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Website", mappedBy="information", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $website;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\SocialNetwork", mappedBy="information", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @Assert\Valid()
     */
    private $socialNetworks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\Phone", mappedBy="information", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\Email", mappedBy="information", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $emails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Information\Legal", mappedBy="information", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $legals;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Information\Address", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="information_addresses",
     *      joinColumns={@ORM\JoinColumn(name="information_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $addresses;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="information_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="information_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     */
    private $i18ns;

    /**
     * Information constructor.
     */
    public function __construct()
    {
        $this->socialNetworks = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->legals = new ArrayCollection();
        $this->addresses = new ArrayCollection();
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
     * @return Collection|SocialNetwork[]
     */
    public function getSocialNetworks(): Collection
    {
        return $this->socialNetworks;
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks[] = $socialNetwork;
            $socialNetwork->setInformation($this);
        }

        return $this;
    }

    public function removeSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if ($this->socialNetworks->removeElement($socialNetwork)) {
            // set the owning side to null (unless already changed)
            if ($socialNetwork->getInformation() === $this) {
                $socialNetwork->setInformation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setInformation($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getInformation() === $this) {
                $phone->setInformation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setInformation($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->removeElement($email)) {
            // set the owning side to null (unless already changed)
            if ($email->getInformation() === $this) {
                $email->setInformation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Legal[]
     */
    public function getLegals(): Collection
    {
        return $this->legals;
    }

    public function addLegal(Legal $legal): self
    {
        if (!$this->legals->contains($legal)) {
            $this->legals[] = $legal;
            $legal->setInformation($this);
        }

        return $this;
    }

    public function removeLegal(Legal $legal): self
    {
        if ($this->legals->removeElement($legal)) {
            // set the owning side to null (unless already changed)
            if ($legal->getInformation() === $this) {
                $legal->setInformation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        $this->addresses->removeElement($address);

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
        $this->i18ns->removeElement($i18n);

        return $this;
    }
}
