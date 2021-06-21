<?php

namespace App\Repository\Gdpr;

use App\Entity\Gdpr\Cookie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CookieRepository
 *
 * @method Cookie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cookie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cookie[]    findAll()
 * @method Cookie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CookieRepository extends ServiceEntityRepository
{
    /**
     * CookieRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cookie::class);
    }
}
