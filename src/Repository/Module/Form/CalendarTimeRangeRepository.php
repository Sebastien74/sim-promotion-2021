<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\CalendarTimeRange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CalendarTimeRangeRepository
 *
 * @method CalendarTimeRange|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarTimeRange|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarTimeRange[]    findAll()
 * @method CalendarTimeRange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarTimeRangeRepository extends ServiceEntityRepository
{
    /**
     * CalendarTimeRangeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarTimeRange::class);
    }
}
