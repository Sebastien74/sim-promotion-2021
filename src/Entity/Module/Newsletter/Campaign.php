<?php

namespace App\Entity\Module\Newsletter;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Module\Newsletter\CampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Campaign
 *
 * @ORM\Table(name="module_newsletter_campaign")
 * @ORM\Entity(repositoryClass=CampaignRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Campaign extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'campaign',
        'buttons' => [
            'admin_newsletteremail_index'
        ]
    ];
    protected static $labels = [
        "admin_newsletteremail_index" => "Emails"
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailConfirmation = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $thanksModal = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recaptcha = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $internalRegistration = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securityKey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $externalFormAction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalFieldEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalFormToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Newsletter\Email", mappedBy="campaign", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $emails;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_newsletter_campaign_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Campaign constructor.
     */
    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    public function getEmailConfirmation(): ?bool
    {
        return $this->emailConfirmation;
    }

    public function setEmailConfirmation(bool $emailConfirmation): self
    {
        $this->emailConfirmation = $emailConfirmation;

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

    public function getRecaptcha(): ?bool
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(bool $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }

    public function getInternalRegistration(): ?bool
    {
        return $this->internalRegistration;
    }

    public function setInternalRegistration(bool $internalRegistration): self
    {
        $this->internalRegistration = $internalRegistration;

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

    public function getExternalFormAction(): ?string
    {
        return $this->externalFormAction;
    }

    public function setExternalFormAction(?string $externalFormAction): self
    {
        $this->externalFormAction = $externalFormAction;

        return $this;
    }

    public function getExternalFieldEmail(): ?string
    {
        return $this->externalFieldEmail;
    }

    public function setExternalFieldEmail(?string $externalFieldEmail): self
    {
        $this->externalFieldEmail = $externalFieldEmail;

        return $this;
    }

    public function getExternalFormToken(): ?string
    {
        return $this->externalFormToken;
    }

    public function setExternalFormToken(?string $externalFormToken): self
    {
        $this->externalFormToken = $externalFormToken;

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
            $email->setCampaign($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->removeElement($email)) {
            // set the owning side to null (unless already changed)
            if ($email->getCampaign() === $this) {
                $email->setCampaign(null);
            }
        }

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
