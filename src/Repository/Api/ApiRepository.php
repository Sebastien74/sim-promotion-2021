<?php

namespace App\Repository\Api;

use App\Entity\Api\Api;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ApiRepository
 *
 * @method Api|null find($id, $lockMode = null, $lockVersion = null)
 * @method Api|null findOneBy(array $criteria, array $orderBy = null)
 * @method Api[]    findAll()
 * @method Api[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ApiRepository extends ServiceEntityRepository
{
    /**
     * ApiRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Api::class);
    }

    /**
     * Find by name
     *
     * @param int $id
     * @return array|null
     */
    public function findByArray(int $id): ?array
    {
        $result = $this->createQueryBuilder('a')
            ->leftJoin('a.google', 'g')
            ->leftJoin('a.facebook', 'f')
            ->leftJoin('g.googleI18ns', 'gi')
            ->leftJoin('f.facebookI18ns', 'fi')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->addSelect('g')
            ->addSelect('f')
            ->addSelect('gi')
            ->addSelect('fi')
            ->getQuery()
            ->getArrayResult();

        return !empty($result[0]) ? $result[0] : NULL;
    }
}
