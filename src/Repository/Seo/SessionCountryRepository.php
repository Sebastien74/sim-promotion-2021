<?php

namespace App\Repository\Seo;

use App\Entity\Seo\SessionCountry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SessionCountryRepository
 *
 * @method SessionCountry|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionCountry|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionCountry[]    findAll()
 * @method SessionCountry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionCountryRepository extends ServiceEntityRepository
{
    /**
     * SessionCountryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionCountry::class);
    }
}
