<?php

namespace App\Repository\Module\Portfolio;

use App\Entity\Module\Portfolio\Listing;
use App\Entity\Module\Portfolio\Card;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CardRepository
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CardRepository extends ServiceEntityRepository
{
    /**
     * CardRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * Find all published by Category order newest
     *
     * @param string $locale
     * @param Website $website
     * @param Listing $listing
     * @return Card[]
     */
    public function findByListing(string $locale, Website $website, Listing $listing)
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
            $qb->andWhere('ca.id IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
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
        $statement = $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('c.website', 'w')
            ->leftJoin('c.urls', 'u')
            ->leftJoin('u.seo', 's')
            ->leftJoin('c.categories', 'ca')
            ->andWhere('c.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('s')
            ->addSelect('ca');

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
