<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Repository\Security\CompanyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Company
 *
 * @ORM\Table(name="security_compagny")
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Company extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'securitycompany'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contactLastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contactFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Assert\Url()
     */
    private $websiteUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $secretKey;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Logo", mappedBy="company", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $logo;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\CompanyAddress", mappedBy="company", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $address;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->secretKey = md5(uniqid() . $this->name);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getContactLastName(): ?string
    {
        return $this->contactLastName;
    }

    public function setContactLastName(?string $contactLastName): self
    {
        $this->contactLastName = $contactLastName;

        return $this;
    }

    public function getContactFirstName(): ?string
    {
        return $this->contactFirstName;
    }

    public function setContactFirstName(?string $contactFirstName): self
    {
        $this->contactFirstName = $contactFirstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(string $websiteUrl): self
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getLogo(): ?Logo
    {
        return $this->logo;
    }

    public function setLogo(?Logo $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getAddress(): ?CompanyAddress
    {
        return $this->address;
    }

    public function setAddress(?CompanyAddress $address): self
    {
        $this->address = $address;

        return $this;
    }
}
