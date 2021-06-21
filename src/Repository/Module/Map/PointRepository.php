<?php

namespace App\Repository\Module\Map;

use App\Entity\Module\Map\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PointRepository
 *
 * @method Point|null find($id, $lockMode = null, $lockVersion = null)
 * @method Point|null findOneBy(array $criteria, array $orderBy = null)
 * @method Point[]    findAll()
 * @method Point[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PointRepository extends ServiceEntityRepository
{
    /**
     * PointRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Point::class);
    }
}
