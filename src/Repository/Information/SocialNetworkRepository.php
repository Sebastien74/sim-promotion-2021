<?php

namespace App\Repository\Information;

use App\Entity\Information\SocialNetwork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SocialNetworkRepository
 *
 * @method SocialNetwork|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialNetwork|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialNetwork[]    findAll()
 * @method SocialNetwork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SocialNetworkRepository extends ServiceEntityRepository
{
    /**
     * SocialNetworkRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialNetwork::class);
    }

    /**
     * Find SocialNetwork by configuration as array
     *
     * @param int $id
     * @return array
     */
    public function findAllArray(int $id): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.information = :information')
            ->setParameter('information', $id)
            ->getQuery()
            ->getArrayResult();
    }
}
