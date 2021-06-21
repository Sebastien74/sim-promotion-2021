<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CatalogRepository
 *
 * @method Catalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Catalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Catalog[]    findAll()
 * @method Catalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogRepository extends ServiceEntityRepository
{
    /**
     * CatalogRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }
}