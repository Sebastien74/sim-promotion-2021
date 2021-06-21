<?php

namespace App\Repository\Layout;

use App\Entity\Layout\FieldConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FieldConfiguration
 *
 * @method FieldConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldConfiguration[]    findAll()
 * @method FieldConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldConfigurationRepository extends ServiceEntityRepository
{
    /**
     * FieldConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldConfiguration::class);
    }
}
