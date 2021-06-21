<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\CalendarSchedule;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormCalendarManager
 *
 * Manage admin Newsletter form
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormCalendarManager
{
    private $entityManager;
    private $translator;

    /**
     * FormCalendarManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Set default schedules
     *
     * @param Calendar $calendar
     */
    public function setSchedules(Calendar $calendar)
    {
        if($calendar->getSchedules()->count() === 0) {

            $days = [
                'monday' => $this->translator->trans('Lundi', [], 'admin'),
                'tuesday' => $this->translator->trans('Mardi', [], 'admin'),
                'wednesday' => $this->translator->trans('Mercredi', [], 'admin'),
                'thursday' => $this->translator->trans('Jeudi', [], 'admin'),
                'friday' => $this->translator->trans('Vendredi', [], 'admin'),
                'saturday' => $this->translator->trans('Samedi', [], 'admin'),
                'sunday' => $this->translator->trans('Dimanche', [], 'admin')
            ];

            $position = 1;
            foreach ($days as $slug => $adminName) {
                $schedule = new CalendarSchedule();
                $schedule->setAdminName($adminName);
                $schedule->setSlug($slug);
                $schedule->setPosition($position);
                $calendar->addSchedule($schedule);
                $position++;
            }

            $this->entityManager->persist($schedule);
            $this->entityManager->flush();
        }
    }
}