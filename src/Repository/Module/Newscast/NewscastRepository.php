<?php

namespace App\Repository\Module\Newscast;

use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Listing;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * NewscastRepository
 *
 * @method Newscast|null find($id, $lockMode = null, $lockVersion = null)
 * @method Newscast|null findOneBy(array $criteria, array $orderBy = null)
 * @method Newscast[]    findAll()
 * @method Newscast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastRepository extends ServiceEntityRepository
{
    /**
     * NewscastRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newscast::class);
    }

    /**
     * Find Newscast by url & locale
     *
     * @param string $url
     * @param Website $website
     * @param string $locale
     * @param bool $preview
     * @return Newscast
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
     * Find all published order newest
     *
     * @param string $locale
     * @param Website $website
     * @param Newscast|null $excludeNewscast
     * @return Newscast[]
     */
    public function findAllPublishedOrderByNewest(string $locale, Website $website, Newscast $excludeNewscast = NULL)
    {
        $qb = $this->optimizedQueryBuilder($locale, $website);

        if ($excludeNewscast) {
            $qb->andWhere('n.id != :excludeId')
                ->setParameter('excludeId', $excludeNewscast->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find all published by Category order newest
     *
     * @param string $locale
     * @param Website $website
     * @param Category $category
     * @param Newscast|null $excludeNewscast
     * @return Newscast[]
     */
    public function finByCategory(string $locale, Website $website, Category $category, Newscast $excludeNewscast = NULL)
    {
        $orderBy = explode('-', $category->getOrderBy());

        $qb = $this->optimizedQueryBuilder($locale, $website, $orderBy[0], strtoupper($orderBy[1]))
            ->setMaxResults($category->getItemsPerPage())
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $category->getId());

        if ($excludeNewscast) {
            $qb->andWhere('n.id != :excludeId')
                ->setParameter('excludeId', $excludeNewscast->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find all published by Category order newest
     *
     * @param string $locale
     * @param Website $website
     * @param Listing $listing
     * @param Newscast|null $excludeNewscast
     * @return Newscast[]
     */
    public function findByListing(string $locale, Website $website, Listing $listing, Newscast $excludeNewscast = NULL)
    {
        if ($listing->getCategories()->isEmpty()) {
            return [];
        }

        $orderBy = explode('-', $listing->getOrderBy());

        $qb = $this->optimizedQueryBuilder($locale, $website, $orderBy[0], strtoupper($orderBy[1]));

        $categoryIds = [];
        foreach ($listing->getCategories() as $key => $category) {
            $categoryIds[] = $category->getId();
        }
        if ($categoryIds) {
            $qb->andWhere('n.category IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        if ($excludeNewscast) {
            $qb->andWhere('n.id != :excludeId')
                ->setParameter('excludeId', $excludeNewscast->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find max result published order newest
     *
     * @param string $locale
     * @param Website $website
     * @param int $limit
     * @return Newscast|Newscast[]
     * @throws NonUniqueResultException
     */
    public function findMaxResultPublishedOrderByNewest(string $locale, Website $website, int $limit = 5)
    {
        $qb = $this->optimizedQueryBuilder($locale, $website)
            ->setMaxResults($limit)
            ->getQuery();

        if ($limit === 1) {
            return $qb->getOneOrNullResult();
        }

        return $qb->getResult();
    }

    /**
     * Find max result published order newest by Category
     *
     * @param string $locale
     * @param Website $website
     * @param Listing $listing
     * @param int $limit
     * @return Newscast|Newscast[]
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

        $categoryIds = [];
        foreach ($listing->getCategories() as $key => $category) {
            $categoryIds[] = $category->getId();
        }
        if ($categoryIds) {
            $qb->andWhere('n.category IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        if ($limit === 1) {
            return $qb->getQuery()
                ->getOneOrNullResult();
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find max result published order newest by Category
     *
     * @param string $locale
     * @param Website $website
     * @param Category $category
     * @param int $limit
     * @return Newscast|Newscast[]
     * @throws NonUniqueResultException
     */
    public function findMaxResultPublishedCategoryOrderByNewest(string $locale, Website $website, Category $category, int $limit = 5)
    {
        $orderBy = explode('-', $category->getOrderBy());
        $sort = !empty($orderBy[0]) ? $orderBy[0] : 'publicationStart';
        $order = !empty($orderBy[1]) ? strtoupper($orderBy[1]) : 'DESC';

        $statement = $this->optimizedQueryBuilder($locale, $website, $sort, $order)
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->setMaxResults($limit)
            ->getQuery();

        if ($limit === 1) {
            return $statement->getOneOrNullResult();
        }

        return $statement->getResult();
    }

    /**
     * Find for SEO
     *
     * @param string $locale
     * @param Website $website
     * @param string $urlCode
     * @return Newscast|Newscast[]
     * @throws NonUniqueResultException
     */
    public function findForSeo(string $locale, Website $website, string $urlCode)
    {
        if (!$urlCode) {
            return NULL;
        }

        return $this->createQueryBuilder('n')
            ->leftJoin('n.urls', 'u')
            ->andWhere('n.website = :website')
            ->andWhere('u.locale = :locale')
            ->andWhere('u.code = :code')
            ->setParameter('locale', $locale)
            ->setParameter('website', $website)
            ->setParameter('code', $urlCode)
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();
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
            ->leftJoin('n.website', 'w')
            ->leftJoin('n.urls', 'u')
            ->leftJoin('u.seo', 's')
            ->leftJoin('n.category', 'c')
            ->andWhere('n.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('s')
            ->addSelect('c');

        if ($sort !== 'category') {
            $statement->orderBy('n.' . $sort, $order);
        }

        if (!$preview) {
            $statement->andWhere('n.publicationStart IS NULL OR n.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('n.publicationEnd IS NULL OR n.publicationEnd > CURRENT_TIMESTAMP()')
                ->andWhere('n.publicationStart IS NOT NULL')
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
        return $qb ?: $this->createQueryBuilder('n');
    }
}