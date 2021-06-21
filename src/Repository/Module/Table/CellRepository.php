<?php

namespace App\Repository\Module\Table;

use App\Entity\Module\Table\Cell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CellRepository
 *
 * @method Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cell[]    findAll()
 * @method Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CellRepository extends ServiceEntityRepository
{
    /**
     * CellRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cell::class);
    }
}
