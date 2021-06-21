<?php

namespace App\Repository\Security;

use App\Entity\Security\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GroupRepository
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupRepository extends ServiceEntityRepository
{
    /**
     * GroupRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Find by Role name
     *
     * @param string $roleName
     * @return Group[]
     */
    public function findByRoleName(string $roleName)
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.roles', 'r')
            ->andWhere('r.name = :name')
            ->setParameter(':name', $roleName)
            ->getQuery()
            ->getResult();
    }
}
