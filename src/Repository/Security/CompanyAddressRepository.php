<?php

namespace App\Repository\Security;

use App\Entity\Security\CompanyAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CompanyAddressRepository
 *
 * @method CompanyAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyAddress[]    findAll()
 * @method CompanyAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CompanyAddressRepository extends ServiceEntityRepository
{
    /**
     * CompanyAddressRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyAddress::class);
    }
}
