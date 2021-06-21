<?php

namespace App\Repository\Module\Agenda;

use App\Entity\Module\Agenda\Information;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * InformationRepository
 *
 * @method Information|null find($id, $lockMode = null, $lockVersion = null)
 * @method Information|null findOneBy(array $criteria, array $orderBy = null)
 * @method Information[]    findAll()
 * @method Information[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationRepository extends ServiceEntityRepository
{
    /**
     * InformationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Information::class);
    }
}
