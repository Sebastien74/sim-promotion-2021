<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SessionCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SessionCityRepository
 *
 * @method SessionCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionCity[]    findAll()
 * @method SessionCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionCityRepository extends ServiceEntityRepository
{
    /**
     * SessionCityRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionCity::class);
    }
}
