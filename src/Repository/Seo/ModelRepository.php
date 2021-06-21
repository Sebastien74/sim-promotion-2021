<?php

namespace App\Repository\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Model;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ModelRepository
 *
 * @method Model|null find($id, $lockMode = null, $lockVersion = null)
 * @method Model|null findOneBy(array $criteria, array $orderBy = null)
 * @method Model[]    findAll()
 * @method Model[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModelRepository extends ServiceEntityRepository
{
    /**
     * ModelRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Model::class);
    }

    /**
     * Find Url as array
     *
     * @param string $locale
     * @param string $classname
     * @param Website $website
     * @return array
     */
	public function findArrayByLocaleClassnameAndWebsite(string $locale, string $classname, Website $website): array
	{
		$result = $this->createQueryBuilder('m')
			->andWhere('m.locale = :locale')
			->andWhere('m.className = :className')
			->andWhere('m.website = :website')
			->setParameter('locale', $locale)
			->setParameter('className', $classname)
			->setParameter('website', $website->getId())
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
	}
}
