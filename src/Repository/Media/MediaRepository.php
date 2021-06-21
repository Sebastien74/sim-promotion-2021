<?php

namespace App\Repository\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MediaRepository
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRepository extends ServiceEntityRepository
{
    /**
     * MediaRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Find by Website and Folder
     *
     * @param Website $website
     * @param Folder|null $folder
     * @return Media[]
     */
    public function findByWebsiteAndFolder(Website $website, Folder $folder = NULL)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->andWhere('m.screen = :screen')
            ->andWhere('m.website = :website')
            ->andWhere('m.name IS NOT NULL')
            ->setParameter('website', $website)
            ->setParameter('screen', 'desktop');

        if(!$folder) {
            $queryBuilder->andWhere('m.folder IS NULL');
        }
        else {
            $queryBuilder->andWhere('m.folder = :folder')
                ->setParameter('folder', $folder);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
