<?php

namespace App\Repository\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\NotFoundUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * NotFoundUrlRepository
 *
 * @method NotFoundUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotFoundUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotFoundUrl[]    findAll()
 * @method NotFoundUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NotFoundUrlRepository extends ServiceEntityRepository
{
    /**
     * NotFoundUrlRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotFoundUrl::class);
    }

    /**
     * Find by category and type
     *
     * @param Website $website
     * @param string $category
     * @param string $type
     *
     * @return Query
     */
    public function findByCategoryTypeQuery(Website $website, string $category, string $type)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.website = :website')
            ->andWhere('n.category = :category')
            ->andWhere('n.type = :type')
            ->andWhere('n.haveRedirection = :haveRedirection')
            ->setParameter('website', $website)
            ->setParameter('category', $category)
            ->setParameter('type', $type)
            ->setParameter('haveRedirection', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery();
    }
}
