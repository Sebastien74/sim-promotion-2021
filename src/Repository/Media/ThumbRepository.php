<?php

namespace App\Repository\Media;

use App\Entity\Media\Thumb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ThumbRepository
 *
 * @method Thumb|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thumb|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thumb[]    findAll()
 * @method Thumb[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbRepository extends ServiceEntityRepository
{
    /**
     * ThumbRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thumb::class);
    }
}
