<?php

namespace App\Repository\Api;

use App\Entity\Api\FacebookI18n;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FacebookI18nRepository
 *
 * @method FacebookI18n|null find($id, $lockMode = null, $lockVersion = null)
 * @method FacebookI18n|null findOneBy(array $criteria, array $orderBy = null)
 * @method FacebookI18n[]    findAll()
 * @method FacebookI18n[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookI18nRepository extends ServiceEntityRepository
{
    /**
     * FacebookI18nRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookI18n::class);
    }
}
