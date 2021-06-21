<?php

namespace App\Repository\Layout;

use App\Entity\Layout\ActionI18n;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ActionI18nRepository
 *
 * @method ActionI18n|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionI18n|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionI18n[]    findAll()
 * @method ActionI18n[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionI18nRepository extends ServiceEntityRepository
{
    /**
     * ActionI18nRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionI18n::class);
    }
}
