<?php

namespace App\Repository\Module\Gallery;

use App\Entity\Module\Gallery\Gallery;
use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * GalleryRepository
 *
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GalleryRepository extends ServiceEntityRepository
{
    /**
     * GalleryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return Campaign|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter, string $locale): ?Gallery
    {
        $statement = $this->createQueryBuilder('g')
            ->leftJoin('g.website', 'w')
            ->leftJoin('g.category', 'c')
            ->leftJoin('g.mediaRelations', 'mr')
            ->leftJoin('mr.i18n', 'mri')
            ->leftJoin('mr.media', 'm')
            ->leftJoin('m.i18ns', 'mi')
            ->andWhere('g.website = :website')
            ->andWhere('mr.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('c')
            ->addSelect('mr')
            ->addSelect('mri')
            ->addSelect('m')
            ->addSelect('mi');

        if (is_numeric($filter)) {
            $statement->andWhere('g.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('g.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
