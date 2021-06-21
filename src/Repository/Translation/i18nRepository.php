<?php

namespace App\Repository\Translation;

use App\Entity\Translation\i18n;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * i18nRepository
 *
 * @method i18n|null find($id, $lockMode = null, $lockVersion = null)
 * @method i18n|null findOneBy(array $criteria, array $orderBy = null)
 * @method i18n[]    findAll()
 * @method i18n[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nRepository extends ServiceEntityRepository
{
    /**
     * i18nRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, i18n::class);
    }
}
