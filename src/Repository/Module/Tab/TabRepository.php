<?php

namespace App\Repository\Module\Tab;

use App\Entity\Module\Tab\Tab;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TabRepository
 *
 * @method Tab|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tab|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tab[]    findAll()
 * @method Tab[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TabRepository extends ServiceEntityRepository
{
    /**
     * TabRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tab::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return Tab|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter, string $locale): ?Tab
    {
        $statement = $this->createQueryBuilder('t')
            ->leftJoin('t.website', 'w')
            ->leftJoin('t.contents', 'c')
            ->leftJoin('c.i18ns', 'i')
            ->leftJoin('c.mediaRelations', 'mr')
            ->leftJoin('mr.media', 'mrm')
            ->andWhere('t.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('c')
            ->addSelect('i')
            ->addSelect('mr')
            ->addSelect('mrm');

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
