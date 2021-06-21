<?php

namespace App\Repository\Security;

use App\Entity\Security\UserFront;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * UserFrontRepository
 *
 * @method UserFront|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFront|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFront[]    findAll()
 * @method UserFront[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserFrontRepository extends ServiceEntityRepository
{
    /**
     * UserFrontRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFront::class);
    }

    /**
     * Find users with token not NULL
     */
    public function findHaveToken()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
