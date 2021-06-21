<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\Lot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LotRepository
 *
 * @method Lot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lot[]    findAll()
 * @method Lot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LotRepository extends ServiceEntityRepository
{
    /**
     * LotRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lot::class);
    }
}
