<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SessionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SessionGroupRepository
 *
 * @method SessionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionGroup[]    findAll()
 * @method SessionGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionGroupRepository extends ServiceEntityRepository
{
    /**
     * SessionGroupRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionGroup::class);
    }
}
