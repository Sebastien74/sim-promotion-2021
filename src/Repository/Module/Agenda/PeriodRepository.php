<?php

namespace App\Repository\Module\Agenda;

use App\Entity\Module\Agenda\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PeriodRepository
 *
 * @method Period|null find($id, $lockMode = null, $lockVersion = null)
 * @method Period|null findOneBy(array $criteria, array $orderBy = null)
 * @method Period[]    findAll()
 * @method Period[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PeriodRepository extends ServiceEntityRepository
{
    /**
     * PeriodRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }
}
