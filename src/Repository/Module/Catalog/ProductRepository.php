<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Category;
use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;

/**
 * ProductRepository
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * ProductRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find all online by locale
     *
     * @param Website $website
     * @param string $locale
     * @param bool $isOnline
     * @param string $sort
     * @param string $order
     * @return Product[]
     */
    public function findAllByLocale(Website $website, string $locale, bool $isOnline = true, string $sort = 'ASC', string $order = 'publicationStart'): array
    {
        return $this->optimizedQueryBuilder($locale, $website, $order, $sort)
            ->andWhere('u.isOnline = :isOnline')
            ->setParameter('isOnline', $isOnline)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by array of ID
     *
     * @return Product[]
     */
    public function findByIds(array $ids = []): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find Newscast by url & locale
     *
     * @param string $url
     * @param Website $website
     * @param string $locale
     * @param bool $preview
     * @return Product
     * @throws NonUniqueResultException
     */
    public function findByUrlAndLocale(string $url, Website $website, string $locale, bool $preview = false)
    {
        return $this->optimizedQueryBuilder($locale, $website, NULL, NULL, $preview)
            ->andWhere('u.isOnline = :isOnline')
            ->andWhere('u.code = :code')
            ->setParameter('isOnline', true)
            ->setParameter('code', $url)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find like by text
     *
     * @param Website $website
     * @param string $locale
     * @param string $search
     * @return Product[]
     */
    public function findLikeInTitle(Website $website, string $locale, string $search)
    {
        return $this->optimizedQueryBuilder($locale, $website)
            ->andWhere('i.title LIKE :search')
            ->setParameter(':search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by Catalog[]
     *
     * @param Website $website
     * @param string $locale
     * @param array|PersistentCollection $catalogs
     * @return Product[]
     */
    public function findOnlineByCatalogs(Website $website, string $locale, $catalogs = []): ?array
    {
        $queryBuilder = $this->optimizedQueryBuilder($locale, $website);

        $catalogIds = [];
        foreach ($catalogs as $key => $catalog) {
            $catalogIds[] = $catalog->getId();
        }

        if($catalogIds) {
            $queryBuilder->leftJoin('p.catalog', 'catalog')
                ->andWhere('catalog.id IN (:catalogId)')
                ->leftJoin('catalog.urls', 'catalog_url')
                ->andWhere('catalog_url.locale = :catalog_url_locale')
                ->andWhere('catalog_url.isOnline = :catalog_url_online')
                ->setParameter('catalogId', $catalogIds)
                ->setParameter('catalog_url_locale', $locale)
                ->setParameter('catalog_url_online', true);
        }

        $products = $queryBuilder
            ->getQuery()
            ->getResult();

        foreach ($products as $key => $product) {
            /** @var Product $product */
            if ($product->getUrls()->count() === 0 || !$product->getUrls()[0]->getIsOnline()) {
                unset($products[$key]);
            }
        }

        return $products;
    }

    /**
     * Find by Category[]
     *
     * @param Website $website
     * @param string $locale
     * @param array|PersistentCollection $categories
     * @return Product[]
     */
    public function findOnlineByCategories(Website $website, string $locale, $categories = []): ?array
    {
        $queryBuilder = $this->optimizedQueryBuilder($locale, $website);

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        if ($categoryIds) {
            $queryBuilder->leftJoin('p.categories', 'cat')
                ->addSelect('cat')
                ->andWhere('cat.id IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        $products = $queryBuilder->addSelect('c')
            ->getQuery()
            ->getResult();

        foreach ($products as $key => $product) {
            /** @var Product $product */
            if ($product->getUrls()->count() === 0 || !$product->getUrls()[0]->getIsOnline()) {
                unset($products[$key]);
            }
        }

        return $products;
    }

    /**
     * Find by Category[]
     *
     * @param Website $website
     * @param string $locale
     * @param array|PersistentCollection $values
     * @param string $condition
     * @return Product[]
     */
    public function findOnlineByValues(Website $website, string $locale, $values = [], string $condition = 'AND'): ?array
    {
        $queryBuilder = $this->optimizedQueryBuilder($locale, $website)
            ->join('p.values', 'v')
            ->join('v.value', 'vv');

        foreach ($values as $key => $value) {
            /** @var FeatureValue $value */
            $keyId = uniqid();
            $rowCondition = $condition === 'OR' && $key > 0 ? 'orWhere' : 'andWhere';
            $queryBuilder->$rowCondition('vv.id = :id' . $keyId)
                ->setParameter('id' . $keyId, $value->getId());
        }

        $products = $queryBuilder
            ->addSelect('v')
            ->addSelect('vv')
            ->getQuery()
            ->getResult();

        foreach ($products as $key => $product) {
            /** @var Product $product */
            if ($product->getUrls()->count() === 0 || !$product->getUrls()[0]->getIsOnline()) {
                unset($products[$key]);
            }
        }

        return $products;
    }

    /**
     * Find by value
     *
     * @param FeatureValue $featureValue
     * @return array
     */
    public function findByValue(FeatureValue $featureValue): ?array
    {
        return $this->createQueryBuilder('p')
            ->join('p.values', 'v')
            ->andWhere('v.value = :value')
            ->setParameter('value', $featureValue)
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    /**
     * Optimized QueryBuilder
     *
     * @param string $locale
     * @param Website $website
     * @param string|null $sort
     * @param string|null $order
     * @param bool $preview
     * @param QueryBuilder|null $qb
     * @return QueryBuilder
     */
    private function optimizedQueryBuilder(
        string $locale,
        Website $website,
        string $order = NULL,
        string $sort = NULL,
        bool $preview = false,
        QueryBuilder $qb = NULL): QueryBuilder
    {
        $sort = $sort ?: 'DESC';
        $order = $order ?: 'publicationStart';

        $statement = $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('p.website', 'w')
            ->leftJoin('p.urls', 'u')
            ->leftJoin('p.i18ns', 'i')
            ->leftJoin('u.seo', 's')
            ->leftJoin('p.catalog', 'c')
            ->leftJoin('p.categories', 'ca')
            ->andWhere('p.website = :website')
            ->andWhere('u.locale = :locale')
            ->andWhere('i.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('i')
            ->addSelect('s')
            ->addSelect('c')
            ->addSelect('ca');

        if ($order === 'title') {
            $statement->orderBy('i.' . $order, $sort);
        } else {
            $statement->orderBy('p.' . $order, $sort);
        }

        if (!$preview) {
            $statement->andWhere('p.publicationStart IS NULL OR p.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('p.publicationEnd IS NULL OR p.publicationEnd > CURRENT_TIMESTAMP()')
                ->andWhere('p.publicationStart IS NOT NULL')
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
    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder('p');
    }
}