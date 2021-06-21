<?php

namespace App\Repository\Media;

use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MediaRelationRepository
 *
 * @method MediaRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaRelation[]    findAll()
 * @method MediaRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelationRepository extends ServiceEntityRepository
{
    /**
     * MediaRelationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaRelation::class);
    }

    /**
     * Get default locale media
     *
     * @param Website $website
     * @param string $category
     * @param string $locale
     * @return Website
     * @throws NonUniqueResultException
     */
    public function findDefaultLocaleCategory(Website $website, string $category, string $locale)
    {
        return $this->createQueryBuilder('mr')
            ->leftJoin('mr.media', 'm')
            ->andWhere('mr.locale = :locale')
            ->andWhere('mr.category = :category')
            ->andWhere('m.website = :website')
            ->setParameter('locale', $locale)
            ->setParameter('category', $category)
            ->setParameter('website', $website)
            ->addSelect('m')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
