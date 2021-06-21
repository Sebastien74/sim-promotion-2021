<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SeoConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SeoConfigurationRepository
 *
 * @method SeoConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeoConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeoConfiguration[]    findAll()
 * @method SeoConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoConfigurationRepository extends ServiceEntityRepository
{
    /**
     * SeoConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoConfiguration::class);
    }
}
