<?php

namespace App\Repository\Information;

use App\Entity\Information\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * AddressRepository
 *
 * @method Address|null find($id, $lockMode = null, $lockVersion = null)
 * @method Address|null findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressRepository extends ServiceEntityRepository
{
    /**
     * AddressRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }
}
