<?php

namespace App\Repository\Module\Making;

use App\Entity\Module\Making\Making;
use App\Entity\Module\Making\Listing;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MakingRepository
 *
 * @method Making|null find($id, $lockMode = null, $lockVersion = null)
 * @method Making|null findOneBy(array $criteria, array $orderBy = null)
 * @method Making[]    findAll()
 * @method Making[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MakingRepository extends ServiceEntityRepository
{
    /**
     * MakingRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Making::class);
    }

    /**
     * Find Newscast by url & locale
     *
     * @param string $url
     * @param Website $website
     * @param string $locale
     * @param bool $preview
     * @return Making
     * @throws NonUniqueResultException
     */
    public function findByUrlAndLocale(string $url, Website $website, string $locale, bool $preview = false)
    {
        return $this->optimizedQueryBuilder($locale, $website, NULL, NULL, $preview)
            ->andWhere('u.code = :code')
            ->setParameter('code', $url)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all published by Category order newest
     *
     * @param string $locale
     * @param Website $website
     * @param Listing $listing
     * @param Making|null $excludeMaking
     * @return Making[]
     */
    public function findByListing(string $locale, Website $website, Listing $listing, Making $excludeMaking = NULL)
    {
        $orderBy = explode('-', $listing->getOrderBy());

        $qb = $this->optimizedQueryBuilder($locale, $website, $orderBy[0], strtoupper($orderBy[1]));

        foreach ($listing->getCategories() as $key => $category) {
            $qb->andWhere('c.id = :categoryId' . $key)
                ->setParameter('categoryId' . $key, $category->getId());
        }

        if ($excludeMaking) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeMaking->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find max result published order newest by Category
     *
     * @param string $locale
     * @param Website $website
     * @param Listing $listing
     * @param int $limit
     * @return Making|Making[]
     * @throws NonUniqueResultException
     */
    public function findMaxResultPublishedListingOrderByNewest(string $locale, Website $website, Listing $listing, int $limit = 5)
    {
        if ($listing->getCategories()->isEmpty()) {
            return NULL;
        }

        $orderBy = explode('-', $listing->getOrderBy());

        $qb = $this->optimizedQueryBuilder($locale, $website, $orderBy[0], strtoupper($orderBy[1]))
            ->setMaxResults($limit);

        foreach ($listing->getCategories() as $key => $category) {
            $qb->andWhere('c.id = :categoryId' . $key)
                ->setParameter('categoryId' . $key, $category->getId());
        }

        if ($limit === 1) {
            return $qb->getQuery()
                ->getOneOrNullResult();
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * PublishedQueryBuilder
     *
     * @param string $locale
     * @param Website $website
     * @param string|null $sort
     * @param string|null $order
     * @param bool $preview
     * @param QueryBuilder|null $qb
     * @return QueryBuilder
     */
    public function optimizedQueryBuilder(
        string $locale,
        Website $website,
        string $sort = NULL,
        string $order = NULL,
        $preview = false,
        QueryBuilder $qb = NULL)
    {
        $sort = $sort ? $sort : 'publicationStart';
        $order = $order ? $order : 'DESC';

        $statement = $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.urls', 'u')
            ->leftJoin('u.seo', 's')
            ->leftJoin('m.category', 'c')
            ->andWhere('m.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('s')
            ->addSelect('c')
            ->orderBy('m.' . $sort, $order);

        if (!$preview) {
            $statement->andWhere('m.publicationStart IS NULL OR m.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('m.publicationEnd IS NULL OR m.publicationEnd > CURRENT_TIMESTAMP()')
                ->andWhere('m.publicationStart IS NOT NULL')
                ->andWhere('u.isOnline = :isOnline')
                ->setParameter('isOnline', true);
        }

        return $statement;
    }

    /**
     * Main QueryBuilder
     *
     * @param QueryBuilder|null $qb
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('m');
    }
}
