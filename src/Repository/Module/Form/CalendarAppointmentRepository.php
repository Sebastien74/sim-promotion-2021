<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\CalendarAppointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CalendarAppointmentRepository
 *
 * @method CalendarAppointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarAppointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarAppointment[]    findAll()
 * @method CalendarAppointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointmentRepository extends ServiceEntityRepository
{
    /**
     * CalendarAppointmentRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarAppointment::class);
    }

    /**
     * Find between dates and Calendar
     *
     * @param Calendar $calendar
     * @return CalendarAppointment[]
     */
    public function findBetweenDatesAndCalendar(Calendar $calendar)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.formcalendar', 'c')
            ->andWhere('a.formcalendar = :calendar')
            ->setParameter('calendar', $calendar)
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
}
