<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Entity\Translation\i18n;
use App\Repository\Module\Form\CalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Calendar
 *
 * @ORM\Table(name="module_form_calendar")
 * @ORM\Entity(repositoryClass=CalendarRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Calendar extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'form';
    protected static $interface = [
        'name' => 'formcalendar',
        'buttons' => [
            'appointments' => 'admin_formcalendarappointment_index'
        ],
    ];
    protected static $labels = [
        "admin_formcalendarappointment_index" => "Rendez-vous"
    ];

    /**
     * @ORM\Column(type="array")
     */
    private $receivingEmails = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $controls = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysPerPage = 3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $frequency = 10;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    protected $startHour;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    protected $endHour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minHours;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxHours;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $reference;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\CalendarAppointment", mappedBy="formcalendar", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $appointments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\CalendarSchedule", mappedBy="formcalendar", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\CalendarException", mappedBy="formcalendar", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     * @Assert\Valid()
     */
    private $exceptions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Form", inversedBy="calendars", fetch="EXTRA_LAZY")
     */
    private $form;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_form_calendar_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="calendar_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->exceptions = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
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

    public function getControls(): ?bool
    {
        return $this->controls;
    }

    public function setControls(bool $controls): self
    {
        $this->controls = $controls;

        return $this;
    }

    public function getDaysPerPage(): ?int
    {
        return $this->daysPerPage;
    }

    public function setDaysPerPage(?int $daysPerPage): self
    {
        $this->daysPerPage = $daysPerPage;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(?int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(?\DateTimeInterface $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(?\DateTimeInterface $endHour): self
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getMinHours(): ?int
    {
        return $this->minHours;
    }

    public function setMinHours(?int $minHours): self
    {
        $this->minHours = $minHours;

        return $this;
    }

    public function getMaxHours(): ?int
    {
        return $this->maxHours;
    }

    public function setMaxHours(?int $maxHours): self
    {
        $this->maxHours = $maxHours;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection|CalendarAppointment[]
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(CalendarAppointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setFormcalendar($this);
        }

        return $this;
    }

    public function removeAppointment(CalendarAppointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getFormcalendar() === $this) {
                $appointment->setFormcalendar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CalendarSchedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(CalendarSchedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setFormcalendar($this);
        }

        return $this;
    }

    public function removeSchedule(CalendarSchedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getFormcalendar() === $this) {
                $schedule->setFormcalendar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CalendarException[]
     */
    public function getExceptions(): Collection
    {
        return $this->exceptions;
    }

    public function addException(CalendarException $exception): self
    {
        if (!$this->exceptions->contains($exception)) {
            $this->exceptions[] = $exception;
            $exception->setFormcalendar($this);
        }

        return $this;
    }

    public function removeException(CalendarException $exception): self
    {
        if ($this->exceptions->removeElement($exception)) {
            // set the owning side to null (unless already changed)
            if ($exception->getFormcalendar() === $this) {
                $exception->setFormcalendar(null);
            }
        }

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
