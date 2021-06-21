<?php

namespace App\Repository\Core;

use App\Entity\Core\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LogRepository
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogRepository extends ServiceEntityRepository
{
    /**
     * LogRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Get last entry
     *
     * @return Log
     * @throws NonUniqueResultException
     */
    public function findLast()
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get unread log
     *
     * @return Log
     * @throws NonUniqueResultException
     */
    public function findUnread()
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.isRead = :isRead')
            ->setParameter(':isRead', false)
            ->orderBy('l.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
