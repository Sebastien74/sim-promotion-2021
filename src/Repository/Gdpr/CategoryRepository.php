<?php

namespace App\Repository\Gdpr;

use App\Entity\Core\Configuration;
use App\Entity\Gdpr\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CategoryRepository
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Get by Website Configuration & locale
     *
     * @param array $configuration
     * @param string $locale
     * @return Category[]
     */
    public function findActiveByConfigurationAndLocale(array $configuration, string $locale): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.gdprgroups', 'g')
            ->leftJoin('g.i18ns', 'i')
            ->andWhere('g.active = :active')
            ->andWhere('i.locale = :locale')
            ->andWhere('c.configuration = :configuration')
            ->setParameter('active', true)
            ->setParameter('locale', $locale)
            ->setParameter('configuration', $configuration['id'])
            ->orderBy('c.id', 'ASC')
            ->addSelect('g')
            ->addSelect('i')
            ->getQuery()
            ->getArrayResult();
    }
}
