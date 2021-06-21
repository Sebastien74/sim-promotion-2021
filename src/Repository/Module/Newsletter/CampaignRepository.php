<?php

namespace App\Repository\Module\Newsletter;

use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CampaignRepository
 *
 * @method Campaign|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campaign|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campaign[]    findAll()
 * @method Campaign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CampaignRepository extends ServiceEntityRepository
{
    /**
     * CampaignRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    /**
     * Find by filter
     *
     * @param Website $website
     * @param string $locale
     * @param string|int $filter
     * @return Campaign|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, string $locale, $filter): ?Campaign
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
