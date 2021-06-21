<?php

namespace App\Repository\Layout;

use App\Entity\Layout\LayoutConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LayoutConfigurationRepository
 *
 * @method LayoutConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method LayoutConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method LayoutConfiguration[]    findAll()
 * @method LayoutConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutConfigurationRepository extends ServiceEntityRepository
{
    /**
     * LayoutConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LayoutConfiguration::class);
    }
}
