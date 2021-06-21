<?php

namespace App\Repository\Core;

use App\Entity\Core\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ModuleRepository
 *
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModuleRepository extends ServiceEntityRepository
{
    /**
     * ModuleRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * Find all as array
     *
     * @return array
     */
    public function findAllArray(): array
    {
        return $this->createQueryBuilder('m')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Find i18ns[]
     *
     * @return array
     */
    public function findForI18ns(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('m.i18ns', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getResult();
    }
}
