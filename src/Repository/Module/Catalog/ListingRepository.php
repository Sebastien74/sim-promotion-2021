<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Module\Catalog\Listing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * ListingRepository
 *
 * @method Listing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Listing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Listing[]    findAll()
 * @method Listing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingRepository extends ServiceEntityRepository
{
    /**
     * ListingRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    /**
     * Find one by filter
     *
     * @param string|int $filter
     * @return Listing|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter($filter): ?Listing
    {
        $statement = $this->createQueryBuilder('l');

        if (is_numeric($filter)) {
            $statement->andWhere('l.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('l.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}