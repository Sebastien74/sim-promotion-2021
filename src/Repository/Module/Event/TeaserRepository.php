<?php

namespace App\Repository\Module\Event;

use App\Entity\Module\Event\Teaser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TeaserRepository
 *
 * @method Teaser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teaser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teaser[]    findAll()
 * @method Teaser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TeaserRepository extends ServiceEntityRepository
{
    /**
     * TeaserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teaser::class);
    }
}
