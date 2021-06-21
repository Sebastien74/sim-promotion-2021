<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Repository\Module\Form\CalendarScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CalendarSchedule
 *
 * @ORM\Table(name="module_form_calendar_schedule")
 * @ORM\Entity(repositoryClass=CalendarScheduleRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarSchedule extends BaseEntity
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\CalendarTimeRange", mappedBy="schedule", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"startHour"="ASC"})
     * @Assert\Valid()
     */
    private $timeRanges;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Calendar", inversedBy="schedules", fetch="EXTRA_LAZY")
     */
    private $formcalendar;

    public function __construct()
    {
        $this->timeRanges = new ArrayCollection();
    }

    /**
     * @return Collection|CalendarTimeRange[]
     */
    public function getTimeRanges(): Collection
    {
        return $this->timeRanges;
    }

    public function addTimeRange(CalendarTimeRange $timeRange): self
    {
        if (!$this->timeRanges->contains($timeRange)) {
            $this->timeRanges[] = $timeRange;
            $timeRange->setSchedule($this);
        }

        return $this;
    }

    public function removeTimeRange(CalendarTimeRange $timeRange): self
    {
        if ($this->timeRanges->removeElement($timeRange)) {
            // set the owning side to null (unless already changed)
            if ($timeRange->getSchedule() === $this) {
                $timeRange->setSchedule(null);
            }
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
