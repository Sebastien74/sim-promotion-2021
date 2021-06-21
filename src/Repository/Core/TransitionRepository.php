<?php

namespace App\Repository\Core;

use App\Entity\Core\Transition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TransitionRepository
 *
 * @method Transition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transition[]    findAll()
 * @method Transition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TransitionRepository extends ServiceEntityRepository
{
    /**
     * TransitionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transition::class);
    }
}
