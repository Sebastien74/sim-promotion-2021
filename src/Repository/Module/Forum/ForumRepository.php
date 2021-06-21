<?php

namespace App\Repository\Module\Forum;

use App\Entity\Module\Forum\Forum;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ForumRepository
 *
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ForumRepository extends ServiceEntityRepository
{
    /**
     * ForumRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @return Forum|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter): ?Forum
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.website', 'w')
            ->leftJoin('f.comments', 'c')
            ->andWhere('f.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('c');

        if (is_numeric($filter)) {
            $statement->andWhere('f.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('f.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
