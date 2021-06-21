<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\Calendar;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CalendarRepository
 *
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarRepository extends ServiceEntityRepository
{
    /**
     * CalendarRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendar::class);
    }

    /**
     * Find first by Website
     *
     * @param Website $website
     * @return Calendar
     * @throws NonUniqueResultException
     */
    public function findFirstByWebsite(Website $website): Calendar
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.form', 'f')
            ->andWhere('f.website = :website')
            ->setParameter('website', $website)
            ->orderBy('c.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
