<?php

namespace App\Repository\Module\Map;

use App\Entity\Module\Map\Map;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MapRepository
 *
 * @method Map|null find($id, $lockMode = null, $lockVersion = null)
 * @method Map|null findOneBy(array $criteria, array $orderBy = null)
 * @method Map[]    findAll()
 * @method Map[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MapRepository extends ServiceEntityRepository
{
    /**
     * MapRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Map::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @return Map|null
     * @throws NonUniqueResultException
     */
    public function findOneByFilter(Website $website, $filter): ?Map
    {
        $statement = $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.points', 'p')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('c.i18ns', 'ci')
            ->leftJoin('p.address', 'a')
            ->leftJoin('p.i18ns', 'pi')
            ->andWhere('m.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('p')
            ->addSelect('c')
            ->addSelect('ci')
            ->addSelect('a')
            ->addSelect('pi');

        if (is_numeric($filter)) {
            $statement->andWhere('m.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('m.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }

	/**
	 * Find default
	 *
	 * @param int $websiteId
	 * @return array
	 */
    public function findDefault(int $websiteId): array
	{
        $result = $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.points', 'p')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('c.i18ns', 'ci')
            ->leftJoin('p.address', 'a')
            ->leftJoin('p.i18ns', 'pi')
            ->leftJoin('p.phones', 'pp')
            ->andWhere('m.website = :website')
            ->andWhere('m.isDefault = :isDefault')
            ->setParameter('website', $websiteId)
            ->setParameter('isDefault', true)
            ->addSelect('w')
            ->addSelect('p')
            ->addSelect('c')
            ->addSelect('ci')
            ->addSelect('a')
            ->addSelect('pp')
            ->addSelect('pi')
			->getQuery()
            ->getArrayResult();

        return !empty($result[0]) ? $result[0] : [];
    }
}
