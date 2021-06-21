<?php

namespace App\Repository\Module\Faq;

use App\Entity\Module\Faq\Faq;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FaqRepository
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FaqRepository extends ServiceEntityRepository
{
    /**
     * FaqRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return Faq|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter, string $locale): ?Faq
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.website', 'w')
            ->leftJoin('f.questions', 'q')
            ->leftJoin('q.i18ns', 'i')
            ->andWhere('f.website = :website')
            ->andWhere('i.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('q')
            ->addSelect('i');

        if (is_numeric($filter)) {
            $statement->andWhere('f.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('f.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
