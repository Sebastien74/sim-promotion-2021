<?php

namespace App\Repository\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * UrlRepository
 *
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlRepository extends ServiceEntityRepository
{
    /**
     * UrlRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

	/**
	 * Find Url as array
	 *
	 * @param int $id
	 * @return array
	 */
    public function findArray(int $id): array
	{
		$result = $this->defaultJoin($this->createQueryBuilder('u'))
			->andWhere('u.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
    }

    /**
     * Find Empty SEO
     *
     * @param Website $website
     * @return array
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countEmptyLocalesSEO(Website $website)
    {
        $counts = [];
        $counts['total'] = 0;

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {

            $counts[$locale] = intval($this->createQueryBuilder("u")
                ->select('count(u.id)')
                ->leftJoin('u.seo', 's')
                ->andWhere('u.website = :website')
                ->andWhere('u.locale = :locale')
                ->andWhere('u.isOnline = :isOnline')
                ->andWhere('s.metaTitle IS NULL OR s.metaDescription IS NULL')
                ->setParameter('website', $website)
                ->setParameter('locale', $locale)
                ->setParameter('isOnline', true)
                ->getQuery()
                ->getSingleScalarResult());

            $counts['total'] = $counts['total'] + $counts[$locale];
        }

        return $counts;
    }

	/**
	 * Get default Join
	 *
	 * @param QueryBuilder $queryBuilder
	 * @return QueryBuilder
	 */
	private function defaultJoin(QueryBuilder $queryBuilder): QueryBuilder
	{
		return $queryBuilder
			->leftJoin('u.website', 'w')
            ->leftJoin('u.seo', 's')
            ->leftJoin('u.indexPage', 'up')
            ->leftJoin('s.mediaRelation', 'mr')
            ->leftJoin('mr.media', 'm')
			->leftJoin('w.configuration', 'c')
			->leftJoin('w.information', 'i')
			->leftJoin('w.seoConfiguration', 'sc')
			->leftJoin('i.i18ns', 'ii')
			->addSelect('w')
            ->addSelect('up')
            ->addSelect('s')
            ->addSelect('mr')
            ->addSelect('m')
			->addSelect('c')
			->addSelect('i')
			->addSelect('sc')
			->addSelect('ii');
	}
}
