<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\Feature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeatureRepository
 *
 * @method Feature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feature[]    findAll()
 * @method Feature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureRepository extends ServiceEntityRepository
{
    /**
     * FeatureRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }
}