<?php

namespace App\Repository\Module\Newscast;

use App\Entity\Module\Newscast\Teaser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

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
     * @param string|int $filter
     * @return null|Teaser
     * @throws NonUniqueResultException
     */
    public function findOneByFilter($filter): ?Teaser
    {
        $statement = $this->createQueryBuilder('t')
            ->leftJoin('t.categories', 'c')
            ->leftJoin('t.website', 'w')
            ->leftJoin('c.newscasts', 'n')
            ->leftJoin('n.urls', 'u')
            ->leftJoin('n.i18ns', 'i')
            ->leftJoin('n.mediaRelations', 'mr')
            ->leftJoin('mr.media', 'm')
            ->addSelect('c')
            ->addSelect('w')
            ->addSelect('n')
            ->addSelect('u')
            ->addSelect('i')
            ->addSelect('mr')
            ->addSelect('m');

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