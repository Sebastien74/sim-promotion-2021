<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Teaser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * TeaserRepository
 *
 * @method Teaser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teaser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teaser[]    findAll()
 * @method Teaser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TeaserRepository extends ServiceEntityRepository
{
    /**
     * TeaserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teaser::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param null|string|int $filter
     * @return Teaser|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter): ?Teaser
    {
        $statement = $this->createQueryBuilder('t')
            ->leftJoin('t.catalogs', 'c')
            ->leftJoin('t.website', 'w')
            ->andWhere('t.website = :website')
            ->setParameter('website', $website)
            ->addSelect('c');

        if (is_numeric($filter)) {
            $statement->andWhere('t.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('t.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}