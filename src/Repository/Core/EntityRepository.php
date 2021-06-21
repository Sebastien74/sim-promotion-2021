<?php

namespace App\Repository\Core;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * EntityRepository
 *
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityRepository extends ServiceEntityRepository
{
    /**
     * EntityRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entity::class);
    }

    /**
     * Find Entity optimized query
     *
     * @param string $classname
     * @param Website|null $website
     * @return Entity|null
     * @throws NonUniqueResultException
     */
    public function optimizedQuery(string $classname, Website $website = NULL)
    {
        if (!$website) {
            return NULL;
        }

        return $this->createQueryBuilder('e')
            ->leftJoin('e.website', 'w')
            ->andWhere('e.className = :className')
            ->andWhere('e.website = :website')
            ->setParameter('className', $classname)
            ->setParameter('website', $website)
            ->addSelect('w')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
