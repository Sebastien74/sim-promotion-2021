<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\CalendarAppointment;
use App\Entity\Module\Form\CalendarSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CalendarScheduleRepository
 *
 * @method CalendarSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarSchedule[]    findAll()
 * @method CalendarSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarScheduleRepository extends ServiceEntityRepository
{
    /**
     * CalendarScheduleRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarSchedule::class);
    }

    /**
     * Find by slugs and Calendar
     *
     * @param Calendar $calendar
     * @param array $slugs
     * @return CalendarAppointment[]
     */
    public function findBySlugsAndCalendar(Calendar $calendar, array $slugs = [])
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.formcalendar', 'c')
            ->andWhere('s.formcalendar = :calendar')
            ->andWhere('s.slug IN (:slugs)')
            ->setParameter('calendar', $calendar)
            ->setParameter('slugs', array_values($slugs))
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
}
