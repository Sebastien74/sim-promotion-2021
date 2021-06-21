<?php

namespace App\Entity\Module\Form;

use App\Entity\Layout\Page;
use App\Repository\Module\Form\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Configuration
 *
 * @ORM\Table(name="module_form_configuration")
 * @ORM\Entity(repositoryClass=ConfigurationRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Configuration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sendingEmail = 'dev@felix-creation.fr';

    /**
     * @ORM\Column(type="array")
     */
    private $receivingEmails = ['sebastien@felix-creation.fr'];

    /**
     * @ORM\Column(type="boolean")
     */
    private $dbRegistration = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ajax = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $thanksModal = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attachmentsInMail = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmEmail = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $uniqueContact = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recaptcha = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $calendarsActive = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securityKey;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maxShipments = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationEnd;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\Form", inversedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $form;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\StepForm", inversedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $stepform;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Page", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $pageRedirection;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSendingEmail(): ?string
    {
        return $this->sendingEmail;
    }

    public function setSendingEmail(string $sendingEmail): self
    {
        $this->sendingEmail = $sendingEmail;

        return $this;
    }

    public function getReceivingEmails(): ?array
    {
        return $this->receivingEmails;
    }

    public function setReceivingEmails(array $receivingEmails): self
    {
        $this->receivingEmails = $receivingEmails;

        return $this;
    }

    public function getDbRegistration(): ?bool
    {
        return $this->dbRegistration;
    }

    public function setDbRegistration(bool $dbRegistration): self
    {
        $this->dbRegistration = $dbRegistration;

        return $this;
    }

    public function getAjax(): ?bool
    {
        return $this->ajax;
    }

    public function setAjax(bool $ajax): self
    {
        $this->ajax = $ajax;

        return $this;
    }

    public function getThanksModal(): ?bool
    {
        return $this->thanksModal;
    }

    public function setThanksModal(bool $thanksModal): self
    {
        $this->thanksModal = $thanksModal;

        return $this;
    }

    public function getAttachmentsInMail(): ?bool
    {
        return $this->attachmentsInMail;
    }

    public function setAttachmentsInMail(bool $attachmentsInMail): self
    {
        $this->attachmentsInMail = $attachmentsInMail;

        return $this;
    }

    public function getConfirmEmail(): ?bool
    {
        return $this->confirmEmail;
    }

    public function setConfirmEmail(bool $confirmEmail): self
    {
        $this->confirmEmail = $confirmEmail;

        return $this;
    }

    public function getUniqueContact(): ?bool
    {
        return $this->uniqueContact;
    }

    public function setUniqueContact(bool $uniqueContact): self
    {
        $this->uniqueContact = $uniqueContact;

        return $this;
    }

    public function getRecaptcha(): ?bool
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(bool $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }

    public function getCalendarsActive(): ?bool
    {
        return $this->calendarsActive;
    }

    public function setCalendarsActive(bool $calendarsActive): self
    {
        $this->calendarsActive = $calendarsActive;

        return $this;
    }

    public function getSecurityKey(): ?string
    {
        return $this->securityKey;
    }

    public function setSecurityKey(?string $securityKey): self
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    public function getMaxShipments(): ?int
    {
        return $this->maxShipments;
    }

    public function setMaxShipments(?int $maxShipments): self
    {
        $this->maxShipments = $maxShipments;

        return $this;
    }

    public function getPublicationStart(): ?\DateTimeInterface
    {
        return $this->publicationStart;
    }

    public function setPublicationStart(?\DateTimeInterface $publicationStart): self
    {
        $this->publicationStart = $publicationStart;

        return $this;
    }

    public function getPublicationEnd(): ?\DateTimeInterface
    {
        return $this->publicationEnd;
    }

    public function setPublicationEnd(?\DateTimeInterface $publicationEnd): self
    {
        $this->publicationEnd = $publicationEnd;

        return $this;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getStepform(): ?StepForm
    {
        return $this->stepform;
    }

    public function setStepform(?StepForm $stepform): self
    {
        $this->stepform = $stepform;

        return $this;
    }

    public function getPageRedirection(): ?Page
    {
        return $this->pageRedirection;
    }

    public function setPageRedirection(?Page $pageRedirection): self
    {
        $this->pageRedirection = $pageRedirection;

        return $this;
    }
}
