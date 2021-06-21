<?php

namespace App\Repository\Core;

use App\Entity\Core\Icon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * IconRepository
 *
 * @method Icon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Icon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Icon[]    findAll()
 * @method Icon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconRepository extends ServiceEntityRepository
{
    /**
     * IconRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Icon::class);
    }
}
