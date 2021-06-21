<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\FeatureValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeatureValueRepository
 *
 * @method FeatureValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeatureValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeatureValue[]    findAll()
 * @method FeatureValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueRepository extends ServiceEntityRepository
{
    /**
     * FeatureValueRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeatureValue::class);
    }
}