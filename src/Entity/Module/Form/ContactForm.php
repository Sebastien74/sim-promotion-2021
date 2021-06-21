<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseInterface;
use App\Repository\Module\Form\ContactFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContactForm
 *
 * @ORM\Table(name="module_form_contact")
 * @ORM\Entity(repositoryClass=ContactFormRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactForm extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'form';
    protected static $interface = [
        'name' => 'formcontact'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tokenExpired = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\CalendarAppointment", mappedBy="contactForm", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $appointment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\ContactValue", mappedBy="contactForm", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $contactValues;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Form", inversedBy="contacts", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $form;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Calendar", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $calendar;

    /**
     * ContactForm constructor.
     */
    public function __construct()
    {
        $this->contactValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenExpired(): ?bool
    {
        return $this->tokenExpired;
    }

    public function setTokenExpired(bool $tokenExpired): self
    {
        $this->tokenExpired = $tokenExpired;

        return $this;
    }

    public function getAppointment(): ?CalendarAppointment
    {
        return $this->appointment;
    }

    public function setAppointment(?CalendarAppointment $appointment): self
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * @return Collection|ContactValue[]
     */
    public function getContactValues(): Collection
    {
        return $this->contactValues;
    }

    public function addContactValue(ContactValue $contactValue): self
    {
        if (!$this->contactValues->contains($contactValue)) {
            $this->contactValues[] = $contactValue;
            $contactValue->setContactForm($this);
        }

        return $this;
    }

    public function removeContactValue(ContactValue $contactValue): self
    {
        if ($this->contactValues->contains($contactValue)) {
            $this->contactValues->removeElement($contactValue);
            // set the owning side to null (unless already changed)
            if ($contactValue->getContactForm() === $this) {
                $contactValue->setContactForm(null);
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

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): self
    {
        $this->calendar = $calendar;

        return $this;
    }
}
