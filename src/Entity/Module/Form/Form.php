<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Translation\i18n;
use App\Repository\Module\Form\FormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form
 *
 * @ORM\Table(name="module_form")
 * @ORM\Entity(repositoryClass=FormRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Form extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $parentMasterField = 'stepform';
    protected static $interface = [
        'name' => 'form',
        'resize' => false,
        'disabledButtons' => true,
        'buttons' => [
            'contacts' => 'admin_formcontact_index',
            'calendars' => 'admin_formcalendar_index'
        ],
        'rolesChecker' => [
            'admin_formcalendar_index' => 'ROLE_FORM_CALENDAR'
        ],
        'buttonsChecker' => [
            'admin_formcalendar_index' => 'configuration.calendarsActive'
        ]
    ];
    protected static $labels = [
        "admin_formcontact_index" => "Contacts",
        "admin_formcalendar_index" => "Calendriers"
    ];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\Configuration", mappedBy="form", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $configuration;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Layout\Layout", inversedBy="form", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     */
    private $layout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\ContactForm", mappedBy="form", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\Calendar", mappedBy="form", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $calendars;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\StepForm", inversedBy="forms", fetch="EXTRA_LAZY")
     */
    private $stepform;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_form_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->calendars = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @return Collection|ContactForm[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(ContactForm $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setForm($this);
        }

        return $this;
    }

    public function removeContact(ContactForm $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getForm() === $this) {
                $contact->setForm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Calendar[]
     */
    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

    public function addCalendar(Calendar $calendar): self
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars[] = $calendar;
            $calendar->setForm($this);
        }

        return $this;
    }

    public function removeCalendar(Calendar $calendar): self
    {
        if ($this->calendars->removeElement($calendar)) {
            // set the owning side to null (unless already changed)
            if ($calendar->getForm() === $this) {
                $calendar->setForm(null);
            }
        }

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
        $this->i18ns->removeElement($i18n);

        return $this;
    }
}