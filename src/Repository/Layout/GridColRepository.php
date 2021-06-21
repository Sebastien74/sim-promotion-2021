<?php

namespace App\Repository\Layout;

use App\Entity\Layout\GridCol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GridColRepository
 *
 * @method GridCol|null find($id, $lockMode = null, $lockVersion = null)
 * @method GridCol|null findOneBy(array $criteria, array $orderBy = null)
 * @method GridCol[]    findAll()
 * @method GridCol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridColRepository extends ServiceEntityRepository
{
    /**
     * GridColRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GridCol::class);
    }
}
