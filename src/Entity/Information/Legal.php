<?php

namespace App\Entity\Information;

use App\Entity\BaseInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Legal
 *
 * @ORM\Table(name="legal")
 * @ORM\Entity(repositoryClass="App\Repository\Information\LegalRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Legal extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'legal'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companyRepresentativeName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $capital;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vatNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siretNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commercialRegisterNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $companyAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $managerName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $managerEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $webmasterName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $webmasterEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hostName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hostAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $protectionOfficerName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $protectionOfficerEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $protectionOfficerAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Information\Information", inversedBy="legals", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $information;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyRepresentativeName(): ?string
    {
        return $this->companyRepresentativeName;
    }

    public function setCompanyRepresentativeName(?string $companyRepresentativeName): self
    {
        $this->companyRepresentativeName = $companyRepresentativeName;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getSiretNumber(): ?string
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(?string $siretNumber): self
    {
        $this->siretNumber = $siretNumber;

        return $this;
    }

    public function getCommercialRegisterNumber(): ?string
    {
        return $this->commercialRegisterNumber;
    }

    public function setCommercialRegisterNumber(?string $commercialRegisterNumber): self
    {
        $this->commercialRegisterNumber = $commercialRegisterNumber;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(?string $companyAddress): self
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getManagerName(): ?string
    {
        return $this->managerName;
    }

    public function setManagerName(?string $managerName): self
    {
        $this->managerName = $managerName;

        return $this;
    }

    public function getManagerEmail(): ?string
    {
        return $this->managerEmail;
    }

    public function setManagerEmail(?string $managerEmail): self
    {
        $this->managerEmail = $managerEmail;

        return $this;
    }

    public function getWebmasterName(): ?string
    {
        return $this->webmasterName;
    }

    public function setWebmasterName(?string $webmasterName): self
    {
        $this->webmasterName = $webmasterName;

        return $this;
    }

    public function getWebmasterEmail(): ?string
    {
        return $this->webmasterEmail;
    }

    public function setWebmasterEmail(?string $webmasterEmail): self
    {
        $this->webmasterEmail = $webmasterEmail;

        return $this;
    }

    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    public function setHostName(?string $hostName): self
    {
        $this->hostName = $hostName;

        return $this;
    }

    public function getHostAddress(): ?string
    {
        return $this->hostAddress;
    }

    public function setHostAddress(?string $hostAddress): self
    {
        $this->hostAddress = $hostAddress;

        return $this;
    }

    public function getProtectionOfficerName(): ?string
    {
        return $this->protectionOfficerName;
    }

    public function setProtectionOfficerName(?string $protectionOfficerName): self
    {
        $this->protectionOfficerName = $protectionOfficerName;

        return $this;
    }

    public function getProtectionOfficerEmail(): ?string
    {
        return $this->protectionOfficerEmail;
    }

    public function setProtectionOfficerEmail(?string $protectionOfficerEmail): self
    {
        $this->protectionOfficerEmail = $protectionOfficerEmail;

        return $this;
    }

    public function getProtectionOfficerAddress(): ?string
    {
        return $this->protectionOfficerAddress;
    }

    public function setProtectionOfficerAddress(?string $protectionOfficerAddress): self
    {
        $this->protectionOfficerAddress = $protectionOfficerAddress;

        return $this;
    }

    public function getInformation(): ?Information
    {
        return $this->information;
    }

    public function setInformation(?Information $information): self
    {
        $this->information = $information;

        return $this;
    }
}