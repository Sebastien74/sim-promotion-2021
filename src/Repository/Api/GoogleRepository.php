<?php

namespace App\Repository\Api;

use App\Entity\Api\Google;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GoogleRepository
 *
 * @method Google|null find($id, $lockMode = null, $lockVersion = null)
 * @method Google|null findOneBy(array $criteria, array $orderBy = null)
 * @method Google[]    findAll()
 * @method Google[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GoogleRepository extends ServiceEntityRepository
{
    /**
     * GoogleRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Google::class);
    }
}
