<?php

namespace App\Repository\Layout;

use App\Entity\Layout\FieldValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FieldValueRepository
 *
 * @method FieldValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldValue[]    findAll()
 * @method FieldValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldValueRepository extends ServiceEntityRepository
{
    /**
     * FieldValueRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldValue::class);
    }
}
