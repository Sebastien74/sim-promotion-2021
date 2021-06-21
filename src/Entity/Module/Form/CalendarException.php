<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Repository\Module\Form\CalendarExceptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarException
 *
 * @ORM\Table(name="module_form_calendar_exception")
 * @ORM\Entity(repositoryClass=CalendarExceptionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarException extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'calendar';
    protected static $interface = [
        'name' => 'formcalendarexception',
    ];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isClose = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Calendar", inversedBy="exceptions", fetch="EXTRA_LAZY")
     */
    private $formcalendar;

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getIsClose(): ?bool
    {
        return $this->isClose;
    }

    public function setIsClose(bool $isClose): self
    {
        $this->isClose = $isClose;

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
