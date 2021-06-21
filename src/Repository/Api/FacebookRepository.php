<?php

namespace App\Repository\Api;

use App\Entity\Api\Facebook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FacebookRepository
 *
 * @method Facebook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facebook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facebook[]    findAll()
 * @method Facebook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookRepository extends ServiceEntityRepository
{
    /**
     * FacebookRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facebook::class);
    }
}
