<?php

namespace App\Repository\Seo;

use App\Entity\Seo\Seo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SeoRepository
 *
 * @method Seo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Seo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Seo[]    findAll()
 * @method Seo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoRepository extends ServiceEntityRepository
{
    /**
     * SeoRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seo::class);
    }
}
