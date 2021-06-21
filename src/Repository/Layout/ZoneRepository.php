<?php

namespace App\Repository\Layout;

use App\Entity\Layout\Layout;
use App\Entity\Layout\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ZoneRepository
 *
 * @method Zone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zone[]    findAll()
 * @method Zone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneRepository extends ServiceEntityRepository
{
    /**
     * ZoneRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    /**
     * Find by Layout
     *
     * @param Layout $layout
     * @return array
     */
    public function findByLayoutArray(Layout $layout): array
    {
        return $this->createQueryBuilder('z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('z.transition', 'zt')
            ->leftJoin('c.transition', 'ct')
            ->leftJoin('c.blocks', 'b')
            ->leftJoin('b.transition', 'bt')
            ->leftJoin('b.blockType', 'bty')
            ->leftJoin('b.action', 'ba')
            ->leftJoin('b.mediaRelations', 'mr')
            ->leftJoin('mr.media', 'm')
            ->leftJoin('m.thumbs', 'mt')
            ->leftJoin('mt.configuration', 'mtc')
            ->andWhere('z.layout = :layout')
            ->setParameter('layout', $layout->getId())
            ->addSelect('c')
            ->addSelect('zt')
            ->addSelect('ct')
            ->addSelect('b')
            ->addSelect('bt')
            ->addSelect('bty')
            ->addSelect('ba')
            ->addSelect('mr')
            ->addSelect('m')
            ->addSelect('m')
            ->addSelect('mt')
            ->addSelect('mtc')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
