<?php

namespace App\Repository\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Category;
use App\Entity\Module\Catalog\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CategoryRepository
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find all online by locale
     *
     * @param Website $website
     * @param string $locale
     * @param string $sort
     * @param string $order
     * @return Product[]
     */
    public function findAllByLocale(Website $website, string $locale, string $sort = 'ASC', string $order = 'position'): array
    {
        return $this->optimizedQueryBuilder($locale, $website, $order, $sort)
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
     * @param QueryBuilder|null $qb
     * @return QueryBuilder
     */
    private function optimizedQueryBuilder(
        string $locale,
        Website $website,
        string $order = NULL,
        string $sort = NULL,
        QueryBuilder $qb = NULL): QueryBuilder
    {
        $sort = $sort ?: 'DESC';
        $order = $order ?: 'position';

        $statement = $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('c.website', 'w')
            ->leftJoin('c.i18ns', 'i')
            ->leftJoin('c.mediaRelations', 'mr')
            ->andWhere('c.website = :website')
            ->andWhere('i.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('i')
            ->addSelect('mr');

        if ($order === 'title') {
            $statement->orderBy('i.' . $order, $sort);
        } else {
            $statement->orderBy('c.' . $order, $sort);
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
        return $qb ?: $this->createQueryBuilder('c');
    }
}