<?php

namespace App\Repository\Gdpr;

use App\Entity\Core\Configuration;
use App\Entity\Gdpr\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GroupRepository
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupRepository extends ServiceEntityRepository
{
    /**
     * GroupRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Get by Category Configuration
     *
     * @param Configuration $configuration
     * @param string $slug
     * @return Group
     * @throws NonUniqueResultException
     */
    public function findByConfiguration(Configuration $configuration, string $slug)
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.gdprcategory', 'c')
            ->addSelect('c')
            ->andWhere('g.slug = :slug')
            ->setParameter(':slug', $slug)
            ->andWhere('c.configuration = :configuration')
            ->setParameter('configuration', $configuration)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
