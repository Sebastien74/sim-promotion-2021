<?php

namespace App\Repository\Module\Slider;

use App\Entity\Module\Slider\Slider;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SliderRepository
 *
 * @method Slider|null find($id, $lockMode = null, $lockVersion = null)
 * @method Slider|null findOneBy(array $criteria, array $orderBy = null)
 * @method Slider[]    findAll()
 * @method Slider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SliderRepository extends ServiceEntityRepository
{
    /**
     * SliderRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slider::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return Slider|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter, string $locale): ?Slider
    {
        $statement = $this->createQueryBuilder('s')
            ->leftJoin('s.website', 'w')
            ->leftJoin('s.mediaRelations', 'mr')
            ->leftJoin('mr.i18n', 'mri')
            ->leftJoin('mr.media', 'm')
            ->leftJoin('m.i18ns', 'mi')
            ->andWhere('s.website = :website')
            ->andWhere('mr.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('mr')
            ->addSelect('mri')
            ->addSelect('m')
            ->addSelect('mi');

        if (is_numeric($filter)) {
            $statement->andWhere('s.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('s.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}