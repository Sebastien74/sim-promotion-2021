<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\FeatureValueProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeatureValueProductRepository
 *
 * @method FeatureValueProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeatureValueProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeatureValueProduct[]    findAll()
 * @method FeatureValueProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueProductRepository extends ServiceEntityRepository
{
    /**
     * FeatureValueProductRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeatureValueProduct::class);
    }
}