<?php

namespace App\Repository\Module\Table;

use App\Entity\Module\Table\Col;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ColRepository
 *
 * @method Col|null find($id, $lockMode = null, $lockVersion = null)
 * @method Col|null findOneBy(array $criteria, array $orderBy = null)
 * @method Col[]    findAll()
 * @method Col[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColRepository extends ServiceEntityRepository
{
    /**
     * ColRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Col::class);
    }
}
