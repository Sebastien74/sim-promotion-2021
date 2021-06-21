<?php

namespace App\Repository\Security;

use App\Entity\Security\UserCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCategory[]    findAll()
 * @method UserCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCategory::class);
    }

    /**
     * Find by i18nSlug
     *
     * @param string $locale
     * @param string|null $slug
     * @return UserCategory
     * @throws NonUniqueResultException
     */
    public function findBySlug(string $locale, string $slug = NULL)
    {
        if (!$slug) {
            return NULL;
        }

        return $this->createQueryBuilder('u')
            ->leftJoin('u.i18ns', 'i')
            ->andWhere('i.locale = :locale')
            ->andWhere('i.slug = :slug')
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->addSelect('i')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
