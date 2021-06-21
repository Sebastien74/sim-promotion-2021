<?php

namespace App\Entity\Core;

use App\Entity\BaseInterface;
use App\Repository\Core\SecurityRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Security
 *
 * @ORM\Table(name="core_security")
 * @ORM\Entity(repositoryClass=SecurityRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Security extends BaseInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $securityKey;

    /**
     * @ORM\Column(type="boolean")
     */
    private $secureWebsite = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adminRegistration = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adminRegistrationValidation = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adminPasswordSecurity = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $adminPasswordDelay = 365;

    /**
     * @ORM\Column(type="boolean")
     */
    private $frontRegistration = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $frontRegistrationValidation = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $frontEmailConfirmation = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $frontPasswordSecurity = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $frontPasswordDelay = 365;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Website", mappedBy="security", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $website;

    /**
     * @ORM\PrePersist
     * @throws Exception
     */
    public function prePersist()
    {
        $this->securityKey = crypt(random_bytes(30), 'rl');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSecurityKey(): ?string
    {
        return $this->securityKey;
    }

    public function setSecurityKey(string $securityKey): self
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    public function getSecureWebsite(): ?bool
    {
        return $this->secureWebsite;
    }

    public function setSecureWebsite(bool $secureWebsite): self
    {
        $this->secureWebsite = $secureWebsite;

        return $this;
    }

    public function getAdminRegistration(): ?bool
    {
        return $this->adminRegistration;
    }

    public function setAdminRegistration(bool $adminRegistration): self
    {
        $this->adminRegistration = $adminRegistration;

        return $this;
    }

    public function getAdminPasswordSecurity(): ?bool
    {
        return $this->adminPasswordSecurity;
    }

    public function setAdminPasswordSecurity(bool $adminPasswordSecurity): self
    {
        $this->adminPasswordSecurity = $adminPasswordSecurity;

        return $this;
    }

    public function getAdminRegistrationValidation(): ?bool
    {
        return $this->adminRegistrationValidation;
    }

    public function setAdminRegistrationValidation(bool $adminRegistrationValidation): self
    {
        $this->adminRegistrationValidation = $adminRegistrationValidation;

        return $this;
    }

    public function getAdminPasswordDelay(): ?int
    {
        return $this->adminPasswordDelay;
    }

    public function setAdminPasswordDelay(?int $adminPasswordDelay): self
    {
        $this->adminPasswordDelay = $adminPasswordDelay;

        return $this;
    }

    public function getFrontRegistration(): ?bool
    {
        return $this->frontRegistration;
    }

    public function setFrontRegistration(bool $frontRegistration): self
    {
        $this->frontRegistration = $frontRegistration;

        return $this;
    }

    public function getFrontRegistrationValidation(): ?bool
    {
        return $this->frontRegistrationValidation;
    }

    public function setFrontRegistrationValidation(bool $frontRegistrationValidation): self
    {
        $this->frontRegistrationValidation = $frontRegistrationValidation;

        return $this;
    }

    public function getFrontEmailConfirmation(): ?bool
    {
        return $this->frontEmailConfirmation;
    }

    public function setFrontEmailConfirmation(bool $frontEmailConfirmation): self
    {
        $this->frontEmailConfirmation = $frontEmailConfirmation;

        return $this;
    }

    public function getFrontPasswordSecurity(): ?bool
    {
        return $this->frontPasswordSecurity;
    }

    public function setFrontPasswordSecurity(bool $frontPasswordSecurity): self
    {
        $this->frontPasswordSecurity = $frontPasswordSecurity;

        return $this;
    }

    public function getFrontPasswordDelay(): ?int
    {
        return $this->frontPasswordDelay;
    }

    public function setFrontPasswordDelay(?int $frontPasswordDelay): self
    {
        $this->frontPasswordDelay = $frontPasswordDelay;

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
}