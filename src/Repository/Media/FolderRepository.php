<?php

namespace App\Repository\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FolderRepository
 *
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FolderRepository extends ServiceEntityRepository
{
    /**
     * FolderRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    /**
     * Find one by Website
     *
     * @param Website $website
     * @param int $id
     * @return Folder|null
     * @throws NonUniqueResultException
     */
    public function findOneByWebsite(Website $website, int $id)
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.medias', 'm')
            ->leftJoin('f.folders', 'fs')
            ->andWhere('f.id = :id')
            ->andWhere('f.website = :website')
            ->setParameter('id', $id)
            ->setParameter('website', $website)
            ->addSelect('m')
            ->addSelect('fs')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find by Website
     *
     * @param Website $website
     * @return Folder[]
     */
    public function findByWebsite(Website $website)
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.medias', 'm')
            ->leftJoin('f.folders', 'fs')
            ->andWhere('f.website = :website')
            ->setParameter('website', $website)
            ->addSelect('m')
            ->addSelect('fs')
            ->getQuery()
            ->getResult();
    }
}
