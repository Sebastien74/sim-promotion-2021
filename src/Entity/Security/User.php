<?php

namespace App\Entity\Security;

use App\Entity\BaseSecurity;
use App\Entity\Core\Website;
use App\Repository\Security\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="security_user")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class User extends BaseSecurity
{
    /**
     * Configurations
     */
    protected static $masterField;
    protected static $interface = [
        'name' => 'user'
    ];
    protected static $labels = [];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $theme;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Picture", inversedBy="user", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $picture;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Profile", inversedBy="user", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $profile;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Security\Company", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="security_users_companies",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $companies;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="security_users_websites",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="website_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $websites;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->websites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(?Picture $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        $this->companies->removeElement($company);

        return $this;
    }

    /**
     * @return Collection|Website[]
     */
    public function getWebsites(): Collection
    {
        return $this->websites;
    }

    public function addWebsite(Website $website): self
    {
        if (!$this->websites->contains($website)) {
            $this->websites[] = $website;
        }

        return $this;
    }

    public function removeWebsite(Website $website): self
    {
        $this->websites->removeElement($website);

        return $this;
    }
}
