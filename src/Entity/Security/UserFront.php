<?php

namespace App\Entity\Security;

use App\Entity\BaseSecurity;
use App\Entity\Core\Website;
use App\Repository\Security\UserFrontRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserFront
 *
 * @ORM\Table(name="security_user_front")
 * @ORM\Entity(repositoryClass=UserFrontRepository::class)
 *
 * @UniqueEntity(
 *     fields={"website", "email"},
 *     errorPath="email",
 *     message="Cet email existe déjà."
 * )
 *
 * @UniqueEntity(
 *     fields={"website", "login"},
 *     errorPath="login",
 *     message="Cet identifiant existe déjà."
 * )
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserFront extends BaseSecurity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'userfront'
    ];
    protected static $labels = [];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Picture", inversedBy="userFront", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $picture;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Profile", inversedBy="userFront", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $profile;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\UserCategory", inversedBy="userFronts", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @Assert\Valid()
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\Company", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function __toString()
    {
        return $this->getLogin();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ?UserCategory
    {
        return $this->category;
    }

    public function setCategory(?UserCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

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
