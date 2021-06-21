<?php

namespace App\Repository\Module\Contact;

use App\Entity\Module\Contact\Contact;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ContactRepository
 *
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactRepository extends ServiceEntityRepository
{
    /**
     * ContactRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return Contact|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter, string $locale): ?Contact
    {
        $statement = $this->createQueryBuilder('c')
            ->leftJoin('c.website', 'w')
            ->leftJoin('c.i18ns', 'i')
            ->andWhere('c.website = :website')
            ->andWhere('i.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('i');

        if (is_numeric($filter)) {
            $statement->andWhere('c.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('c.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }
}
