<?php

namespace App\Repository\Information;

use App\Entity\Information\Legal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LegalRepository
 *
 * @method Legal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Legal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Legal[]    findAll()
 * @method Legal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LegalRepository extends ServiceEntityRepository
{
    /**
     * LegalRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Legal::class);
    }
}
