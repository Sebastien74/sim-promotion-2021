<?php

namespace App\Repository\Module\Timeline;

use App\Entity\Module\Timeline\Step;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * StepRepository
 *
 * @method Step|null find($id, $lockMode = null, $lockVersion = null)
 * @method Step|null findOneBy(array $criteria, array $orderBy = null)
 * @method Step[]    findAll()
 * @method Step[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepRepository extends ServiceEntityRepository
{
    /**
     * StepRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Step::class);
    }
}
