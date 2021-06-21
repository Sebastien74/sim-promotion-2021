<?php

namespace App\Repository\Layout;

use App\Entity\Layout\Layout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LayoutRepository
 *
 * @method Layout|null find($id, $lockMode = null, $lockVersion = null)
 * @method Layout|null findOneBy(array $criteria, array $orderBy = null)
 * @method Layout[]    findAll()
 * @method Layout[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutRepository extends ServiceEntityRepository
{
    /**
     * LayoutRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Layout::class);
    }
}
