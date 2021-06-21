<?php

namespace App\Repository\Module\Timeline;

use App\Entity\Module\Timeline\Timeline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TimelineRepository
 *
 * @method Timeline|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timeline|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timeline[]    findAll()
 * @method Timeline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TimelineRepository extends ServiceEntityRepository
{
    /**
     * TimelineRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timeline::class);
    }

    /**
     * Find one by filter
     *
     * @param string|int $filter
     * @return null|Timeline
     * @throws NonUniqueResultException
     */
    public function findOneByFilter($filter): ?Timeline
    {
        $statement = $this->createQueryBuilder('a')
            ->leftJoin('a.website', 'w')
            ->addSelect('w');

        if (is_numeric($filter)) {
            $statement->andWhere('a.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('a.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
