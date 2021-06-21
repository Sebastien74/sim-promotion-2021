<?php

namespace App\Repository\Layout;

use App\Entity\Layout\Grid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GridRepository
 *
 * @method Grid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Grid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Grid[]    findAll()
 * @method Grid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridRepository extends ServiceEntityRepository
{
    /**
     * GridRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grid::class);
    }
}
