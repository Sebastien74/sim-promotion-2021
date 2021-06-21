<?php

namespace App\Repository\Module\Portfolio;

use App\Entity\Module\Newscast\Newscast;
use App\Entity\Module\Portfolio\Category;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
     * Find Category by url & locale
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
        $statement = $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('c.website', 'w')
            ->leftJoin('c.urls', 'u')
            ->leftJoin('u.seo', 's')
            ->andWhere('c.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('s');

        if (!$preview) {
            $statement->andWhere('c.publicationStart IS NULL OR c.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('c.publicationEnd IS NULL OR c.publicationEnd > CURRENT_TIMESTAMP()')
                ->andWhere('c.publicationStart IS NOT NULL')
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
        return $qb ?: $this->createQueryBuilder('c');
    }
}
