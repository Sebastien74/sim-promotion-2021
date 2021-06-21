<?php

namespace App\Repository\Module\Agenda;

use App\Entity\Module\Agenda\Agenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * AgendaRepository
 *
 * @method Agenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agenda[]    findAll()
 * @method Agenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AgendaRepository extends ServiceEntityRepository
{
    /**
     * AgendaRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    /**
     * Find one by filter
     *
     * @param string|int $filter
     * @return null|Agenda
     * @throws NonUniqueResultException
     */
    public function findOneByFilter($filter): ?Agenda
    {
        $statement = $this->createQueryBuilder('a')
            ->leftJoin('a.website', 'w')
            ->addSelect('w');

        if (is_numeric($filter)) {
            $statement->andWhere('a.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('a.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
