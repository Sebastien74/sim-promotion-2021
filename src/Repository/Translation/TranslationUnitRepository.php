<?php

namespace App\Repository\Translation;

use App\Entity\Translation\TranslationUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TranslationUnitRepository
 *
 * @method TranslationUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslationUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslationUnit[]    findAll()
 * @method TranslationUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationUnitRepository extends ServiceEntityRepository
{
    /**
     * TranslationUnitRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationUnit::class);
    }
}
