<?php

namespace App\Repository\Core;

use App\Entity\Core\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SecurityRepository
 *
 * @method Security|null find($id, $lockMode = null, $lockVersion = null)
 * @method Security|null findOneBy(array $criteria, array $orderBy = null)
 * @method Security[]    findAll()
 * @method Security[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecurityRepository extends ServiceEntityRepository
{
    /**
     * ConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Security::class);
    }
}
