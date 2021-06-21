<?php

namespace App\Form\Manager\Front;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\CalendarAppointment;
use App\Entity\Module\Form\CalendarException;
use App\Entity\Module\Form\CalendarSchedule;
use App\Entity\Module\Form\ContactForm;
use App\Entity\Core\Website;
use App\Service\Core\MailerService;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * FormCalendarManager
 *
 * Manage front Form Calendar
 *
 * @property int DAYS_NUMBER
 * @property array UN_WORK_DAYS
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property KernelInterface $kernel
 * @property MailerService $mailer
 * @property FormManager $formManager
 * @property Calendar $calendar
 * @property array $disableSlots
 * @property array $laterSlots
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormCalendarManager
{
    private const DAYS_NUMBER = 3;
    private const UN_WORK_DAYS = ['sunday'];

    private $entityManager;
    private $request;
    private $kernel;
    private $mailer;
    private $formManager;
    private $calendar;
    private $disableSlots = [];

    /**
     * FormCalendarManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param MailerService $mailer
     * @param FormManager $formManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        KernelInterface $kernel,
        MailerService $mailer,
        FormManager $formManager)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
        $this->kernel = $kernel;
        $this->mailer = $mailer;
        $this->formManager = $formManager;
    }

    /**
     * Set current Calendar
     *
     * @param Website $website
     * @param ContactForm|null $contact
     * @return Calendar|mixed|object|null
     */
    public function setCalendar(Website $website, ContactForm $contact = NULL)
    {
        $requestCalendar = $this->request->get('calendar');

        $this->calendar = $contact instanceof ContactForm ? $contact->getCalendar() : NULL;

        if (!$this->calendar && $requestCalendar) {
            $this->calendar = $this->entityManager->getRepository(Calendar::class)->find($requestCalendar);
        } elseif (!$this->calendar) {
            $this->calendar = $this->entityManager->getRepository(Calendar::class)->findFirstByWebsite($website);
        }

        if ($contact && !$contact->getCalendar()) {
            $contact->setCalendar($this->calendar);
            $this->entityManager->persist($contact);
            $this->entityManager->flush();
        }

        return $this->calendar;
    }

    /**
     * Get render dates
     *
     * @param ContactForm|null $contact
     * @return false|object
     * @throws Exception
     */
    public function getDates(ContactForm $contact = NULL)
    {
        if (!$this->calendar instanceof Calendar) {
            return false;
        }

        $daysNumbers = $contact && $this->calendar->getDaysPerPage() ? $this->calendar->getDaysPerPage() : self::DAYS_NUMBER;
        $startRequest = $this->request->get('startDate');
        $start = $startRequest ? new DateTime($startRequest) : new DateTime('now');
        $currentDate = new DateTime('now');
        $limitDates = $this->getLimitDates($currentDate, $start, $daysNumbers);

        $this->getDisableSlots($start, $daysNumbers);

        $dates[$start->format('Y-m-d')]['datetime'] = $start;
        $dates[$start->format('Y-m-d')]['occurrences'] = $this->getOccurrences($start, $limitDates->minDatetime, $limitDates->maxDatetime);
        for ($i = 1; $i <= ($daysNumbers - 1); $i++) {
            $date = new DateTime($start->format('Y-m-d') . ' +' . $i . ' day');
            $dates[$date->format('Y-m-d')]['datetime'] = $date;
            $dates[$date->format('Y-m-d')]['occurrences'] = $this->getOccurrences($date, $limitDates->minDatetime, $limitDates->maxDatetime);
        }

        $previous = $start->format('Y-m-d') !== $currentDate->format('Y-m-d')
            ? new DateTime($start->format('Y-m-d') . ' -' . $daysNumbers . ' day') : NULL;
        $next = new DateTime($start->format('Y-m-d') . ' +' . $daysNumbers . ' day');

        return (object)[
            'dates' => $dates,
            'start' => $start,
            'previous' => $previous,
            'next' => $next
        ];
    }

    /**
     * ContactForm Appointment registration
     *
     * @param FormInterface $formCalendar
     * @param ContactForm|null $contact
     * @return string
     * @throws Exception
     */
    public function register(FormInterface $formCalendar, ContactForm $contact = NULL)
    {
        if ($contact && $formCalendar->isSubmitted() && $formCalendar->isValid()) {

            $slotDate = $formCalendar->getData()['slot_date'];
            $date = new DateTime(urldecode($slotDate));
            $existing = $this->entityManager->getRepository(CalendarAppointment::class)->findOneBy([
                'appointmentDate' => $date,
                'formcalendar' => $this->calendar
            ]);

            if (!$existing) {

                $appointment = new CalendarAppointment();
                $appointment->setContactForm($contact);
                $appointment->setAppointmentDate($date);
                $appointment->setFormcalendar($this->calendar);
                $appointment->setPosition($this->calendar->getAppointments()->count() + 1);

                $contact->setCalendar($this->calendar);
                $contact->setTokenExpired(true);

                $this->entityManager->persist($contact);
                $this->entityManager->persist($appointment);
                $this->entityManager->flush();

                $this->sendEmail($contact);

                return 'success';
            } else {
                return 'no-available';
            }
        }

        return false;
    }

    /**
     * Get limit dates
     *
     * @param DateTime $currentDate
     * @param DateTime $startDate
     * @param int $daysNumbers
     * @return object
     * @throws Exception
     */
    private function getLimitDates(DateTime $currentDate, DateTime $startDate, int $daysNumbers)
    {
        $minHours = $this->calendar->getMinHours();
        $maxHours = $this->calendar->getMaxHours();

        $start = new DateTime($startDate->format('Y-m-d 00:00'));
        $end = new DateTime($start->format('Y-m-d') . ' +' . $daysNumbers . ' day');
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $datetime) {
            if ($maxHours && in_array($this->getDayCode($datetime->format('w')), self::UN_WORK_DAYS)) {
                $maxHours = $maxHours + 24;
            }
        }

        $minDatetime = $minHours
            ? new DateTime($currentDate->format('Y-m-d H:i:s') . ' +' . $minHours . ' hours')
            : NULL;
        $maxDatetime = $maxHours
            ? new DateTime($currentDate->format('Y-m-d H:i:s') . ' +' . $maxHours . ' hours')
            : NULL;

        return (object)[
            'minDatetime' => $minDatetime,
            'maxDatetime' => $maxDatetime
        ];
    }

    /**
     * Get existing Appointment[]
     *
     * @param DateTime $startDate
     * @param int $daysNumbers
     * @throws Exception
     */
    private function getDisableSlots(DateTime $startDate, int $daysNumbers)
    {
        $start = new DateTime($startDate->format('Y-m-d 00:00'));
        $end = new DateTime($start->format('Y-m-d') . ' +' . $daysNumbers . ' day');
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);

        /** Get Appointments */
        $appointmentsDb = $this->entityManager->getRepository(CalendarAppointment::class)->findBetweenDatesAndCalendar($this->calendar);
        foreach ($appointmentsDb as $appointment) {
            $this->disableSlots[] = $appointment->getAppointmentDate()->format('Y-m-d H:i:s');
        }

        /** Get Schedules */
        $schedulesDays = [];
        foreach ($period as $datetime) {
            $schedulesDays[] = $this->getDayCode($datetime->format('w'));
        }
        $schedulesDb = $this->entityManager->getRepository(CalendarSchedule::class)->findBySlugsAndCalendar($this->calendar, $schedulesDays);
        foreach ($schedulesDb as $schedule) {
            $this->getDisableSchedulesSlots($period, $schedule);
        }

        /** Get Exceptions */
        $this->getDisableExceptionsSlots();
    }

    /**
     * Get disabled schedules slots
     *
     * @param DatePeriod $period
     * @param CalendarSchedule $schedule
     * @throws Exception
     */
    private function getDisableSchedulesSlots(DatePeriod $period, CalendarSchedule $schedule)
    {
        $currentDate = NULL;
        foreach ($period as $datetime) {
            if ($this->getDayCode($datetime->format('w')) === $schedule->getSlug()) {
                $currentDate = $datetime;
                break;
            }
        }

        $openingTimes = [];
        foreach ($schedule->getTimeRanges() as $timeRange) {
            $start = $timeRange->getStartHour();
            $end = $timeRange->getEndHour();
            if ($start && $end) {
                $interval = DateInterval::createFromDateString('1 minute');
                $period = new DatePeriod($start, $interval, $end);
                foreach ($period as $datetime) {
                    $openingTimes[] = $currentDate->format('Y-m-d') . ' ' . $datetime->format('H:i:s');
                }
            }
        }

        $start = new DateTime($currentDate->format('Y-m-d 00:00'));
        $end = new DateTime($currentDate->format('Y-m-d 23:59'));
        $interval = DateInterval::createFromDateString('1 minute');
        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $datetime) {
            if (!in_array($datetime->format('Y-m-d H:i:s'), $openingTimes) || empty($openingTimes)) {
                $this->disableSlots[] = $datetime->format('Y-m-d H:i:s');
            }
        }
    }

    /**
     * Get disabled Exceptions slots
     *
     * @throws Exception
     */
    private function getDisableExceptionsSlots()
    {
        $exceptions = $this->entityManager->getRepository(CalendarException::class)->findBy(['formcalendar' => $this->calendar]);

        foreach ($exceptions as $exception) {

            $start = $end = NULL;
            $startDate = $exception->getStartDate();
            $endDate = $exception->getEndDate();

            /** Close day */
            if ($exception->getIsClose() && $startDate) {
                $start = new DateTime($startDate->format('Y-m-d 00:00'));
                $end = $endDate ? new DateTime($endDate->format('Y-m-d 18:00')) : $startDate->format('Y-m-d 23:59');
            } /** Schedules */
            elseif ($startDate && $endDate) {
                $start = new DateTime($startDate->format('Y-m-d H:i'));
                $end = new DateTime($endDate->format('Y-m-d H:i'));
            }

            if ($start && $end) {
                $interval = DateInterval::createFromDateString('1 minutes');
                $period = new DatePeriod($start, $interval, $end);
                foreach ($period as $datetime) {
                    $this->disableSlots[] = $datetime->format('Y-m-d H:i:s');
                }
            }
        }
    }

    /**
     * Get occurrences
     *
     * @param DateTime $dateTime
     * @param DateTime|null $minDatetime
     * @param DateTime|null $maxDatetime
     * @return array
     * @throws Exception
     */
    private function getOccurrences(DateTime $dateTime, DateTime $minDatetime = NULL, DateTime $maxDatetime = NULL)
    {
        $startHour = $this->calendar->getStartHour() instanceof DateTime ? $this->calendar->getStartHour()->format('H:i') : '08:00';
        $start = new DateTime($dateTime->format('Y-m-d ' . $startHour));
        $endHour = $this->calendar->getEndHour() instanceof DateTime ? $this->calendar->getEndHour()->format('H:i') : '20:00';
        $end = new DateTime($dateTime->format('Y-m-d ' . $endHour));
        $interval = DateInterval::createFromDateString($this->calendar->getFrequency() . ' minutes');
        $period = new DatePeriod($start, $interval, $end);

        $occurrences = [];
        foreach ($period as $datetime) {
            $status = !in_array($datetime->format('Y-m-d H:i:s'), $this->disableSlots) ? 'available' : 'unavailable';
            if ($status == 'available' && $maxDatetime && $datetime > $maxDatetime) {
                $status = 'later';
            } elseif ($status == 'available' && $minDatetime && $datetime < $minDatetime) {
                $status = 'unavailable';
            }
            $occurrences[] = [
                'datetime' => $datetime,
                'available' => $status
            ];
        }

        return $occurrences;
    }

    /**
     * Get day code by key
     *
     * @param $key
     * @return string
     */
    private function getDayCode($key)
    {
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            0 => 'sunday'
        ];

        return $days[$key];
    }

    /**
     * Send email
     *
     * @param ContactForm $contact
     */
    private function sendEmail(ContactForm $contact)
    {
        $form = $contact->getForm();

        if ($form->getConfiguration()->getConfirmEmail()) {

            $website = $form->getWebsite();
            $frontTemplate = $website->getConfiguration()->getTemplate();
            $companyName = $this->formManager->getCompanyName($website);
            $calendarI18n = $this->formManager->getI18n($this->calendar);
            $formI18n = $this->formManager->getI18n($form);
            $subject = $calendarI18n->subject ? $calendarI18n->subject : ($formI18n->subject ? $formI18n->subject : $companyName);

            $this->mailer->setSubject($companyName . ' - ' . $subject);
            $this->mailer->setTo([$contact->getEmail()]);
            $this->mailer->setName($companyName);
            $this->mailer->setFrom($form->getConfiguration()->getSendingEmail());
            $this->mailer->setSender($form->getConfiguration()->getSendingEmail());
            $this->mailer->setTemplate('front/' . $frontTemplate . '/actions/form/email/calendar.html.twig');
            $this->mailer->setArguments([
                'contact' => $contact,
                'calendar' => $contact->getCalendar(),
                'calendarI18n' => $calendarI18n,
                'formI18n' => $formI18n,
                'appointmentDate' => $contact->getAppointment()->getAppointmentDate(),
            ]);
            $this->mailer->send();
        }
    }
}