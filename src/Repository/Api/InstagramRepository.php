<?php

namespace App\Repository\Api;

use App\Entity\Api\Instagram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * InstagramRepository
 *
 * @method Instagram|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instagram|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instagram[]    findAll()
 * @method Instagram[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InstagramRepository extends ServiceEntityRepository
{
    /**
     * InstagramRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instagram::class);
    }
}
