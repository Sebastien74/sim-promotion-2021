<?php

namespace App\Repository\Api;

use App\Entity\Api\GoogleI18n;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GoogleI18nRepository
 *
 * @method GoogleI18n|null find($id, $lockMode = null, $lockVersion = null)
 * @method GoogleI18n|null findOneBy(array $criteria, array $orderBy = null)
 * @method GoogleI18n[]    findAll()
 * @method GoogleI18n[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GoogleI18nRepository extends ServiceEntityRepository
{
    /**
     * GoogleI18nRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GoogleI18n::class);
    }
}
