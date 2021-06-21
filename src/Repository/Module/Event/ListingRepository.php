<?php

namespace App\Repository\Module\Event;

use App\Entity\Module\Event\Listing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ListingRepository
 *
 * @method Listing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Listing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Listing[]    findAll()
 * @method Listing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingRepository extends ServiceEntityRepository
{
    /**
     * ListingRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }
}
