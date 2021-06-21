<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Repository\Module\Form\CalendarTimeRangeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarTimeRange
 *
 * @ORM\Table(name="module_form_calendar_time_range")
 * @ORM\Entity(repositoryClass=CalendarTimeRangeRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarTimeRange extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'schedule';
    protected static $interface = [
        'name' => 'formcalendartimerange',
    ];

    /**
     * @ORM\Column(type="time")
     */
    protected $startHour;

    /**
     * @ORM\Column(type="time")
     */
    protected $endHour;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\CalendarSchedule", inversedBy="timeRanges", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $schedule;

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(\DateTimeInterface $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(\DateTimeInterface $endHour): self
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getSchedule(): ?CalendarSchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?CalendarSchedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }
}
