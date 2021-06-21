<?php

namespace App\Repository\Api;

use App\Entity\Api\Custom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CustomRepository
 *
 * @method Custom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Custom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Custom[]    findAll()
 * @method Custom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CustomRepository extends ServiceEntityRepository
{
    /**
     * CustomRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Custom::class);
    }
}
