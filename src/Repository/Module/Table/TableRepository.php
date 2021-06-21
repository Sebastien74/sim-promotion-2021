<?php

namespace App\Repository\Module\Table;

use App\Entity\Module\Table\Table;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TableRepository
 *
 * @method Table|null find($id, $lockMode = null, $lockVersion = null)
 * @method Table|null findOneBy(array $criteria, array $orderBy = null)
 * @method Table[]    findAll()
 * @method Table[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableRepository extends ServiceEntityRepository
{
    /**
     * TableRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Table::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @return Table|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter): ?Table
    {
        $statement = $this->createQueryBuilder('t')
            ->leftJoin('t.website', 'w')
            ->leftJoin('t.cols', 'c')
            ->leftJoin('c.cells', 'cl')
            ->leftJoin('cl.i18ns', 'i')
            ->andWhere('t.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('c')
            ->addSelect('cl')
            ->addSelect('i');

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