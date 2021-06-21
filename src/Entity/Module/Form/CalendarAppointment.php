<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Repository\Module\Form\CalendarAppointmentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarAppointment
 *
 * @ORM\Table(name="module_form_calendar_appointment")
 * @ORM\Entity(repositoryClass=CalendarAppointmentRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointment extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'formcalendar';
    protected static $interface = [
        'name' => 'formcalendarappointment'
    ];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $appointmentDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\ContactForm", inversedBy="appointment", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $contactForm;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Calendar", inversedBy="appointments", fetch="EXTRA_LAZY")
     */
    private $formcalendar;

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(?\DateTimeInterface $appointmentDate): self
    {
        $this->appointmentDate = $appointmentDate;

        return $this;
    }

    public function getContactForm(): ?ContactForm
    {
        return $this->contactForm;
    }

    public function setContactForm(?ContactForm $contactForm): self
    {
        $this->contactForm = $contactForm;

        // set (or unset) the owning side of the relation if necessary
        $newAppointment = null === $contactForm ? null : $this;
        if ($contactForm->getAppointment() !== $newAppointment) {
            $contactForm->setAppointment($newAppointment);
        }

        return $this;
    }

    public function getFormcalendar(): ?Calendar
    {
        return $this->formcalendar;
    }

    public function setFormcalendar(?Calendar $formcalendar): self
    {
        $this->formcalendar = $formcalendar;

        return $this;
    }
}