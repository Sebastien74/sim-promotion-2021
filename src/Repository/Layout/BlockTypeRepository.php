<?php

namespace App\Repository\Layout;

use App\Entity\Layout\BlockType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * BlockTypeRepository
 *
 * @method BlockType|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlockType|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlockType[]    findAll()
 * @method BlockType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockTypeRepository extends ServiceEntityRepository
{
    /**
     * BlockTypeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockType::class);
    }

    /**
     * Find i18ns[]
     *
     * @return array
     */
    public function findForI18ns(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b')
            ->leftJoin('b.i18ns', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getResult();
    }
}
