<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\ListingFeatureValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ListingFeatureValueRepository
 *
 * @method ListingFeatureValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListingFeatureValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListingFeatureValue[]    findAll()
 * @method ListingFeatureValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingFeatureValueRepository extends ServiceEntityRepository
{
    /**
     * ListingFeatureValueRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListingFeatureValue::class);
    }
}