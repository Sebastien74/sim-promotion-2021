<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\StepForm;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * StepFormRepository
 *
 * @method StepForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method StepForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method StepForm[]    findAll()
 * @method StepForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepFormRepository extends ServiceEntityRepository
{
    /**
     * StepFormRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StepForm::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string $locale
     * @param string|int $filter
     * @return StepForm|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, string $locale, $filter): ?StepForm
    {
        $statement = $this->createQueryBuilder('sf')
            ->leftJoin('sf.website', 'w')
            ->andWhere('sf.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w');

        if (is_numeric($filter)) {
            $statement->andWhere('sf.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('sf.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
