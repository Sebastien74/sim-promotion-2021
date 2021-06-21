<?php

namespace App\Repository\Module\Search;

use App\Entity\Module\Search\Search;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SearchRepository
 *
 * @method Search|null find($id, $lockMode = null, $lockVersion = null)
 * @method Search|null findOneBy(array $criteria, array $orderBy = null)
 * @method Search[]    findAll()
 * @method Search[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchRepository extends ServiceEntityRepository
{
    /**
     * SearchRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Search::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @return Search|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter): ?Search
    {
        $statement = $this->createQueryBuilder('s')
            ->leftJoin('s.website', 'w')
            ->andWhere('s.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w');

        if (is_numeric($filter)) {
            $statement->andWhere('s.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('s.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}