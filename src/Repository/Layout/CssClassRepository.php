<?php

namespace App\Repository\Layout;

use App\Entity\Layout\CssClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CssClassRepository
 *
 * @method CssClass|null find($id, $lockMode = null, $lockVersion = null)
 * @method CssClass|null findOneBy(array $criteria, array $orderBy = null)
 * @method CssClass[]    findAll()
 * @method CssClass[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CssClassRepository extends ServiceEntityRepository
{
    /**
     * CssClassRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CssClass::class);
    }
}
