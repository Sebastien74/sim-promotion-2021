<?php

namespace App\Repository\Core;

use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * WebsiteRepository
 *
 * @method Website|null find($id, $lockMode = null, $lockVersion = null)
 * @method Website|null findOneBy(array $criteria, array $orderBy = null)
 * @method Website[]    findAll()
 * @method Website[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @property string $host
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteRepository extends ServiceEntityRepository
{
    private $host;

    /**
     * WebsiteRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param RequestStack $requestStack
     */
    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Website::class);

        $request = $requestStack->getMasterRequest();
        if ($request) {
            $this->host = $request->getHost();
        }
    }

    /**
     * Get Website
     *
     * @param int $id
     * @return Website|null
     * @throws NonUniqueResultException
     */
    public function findObject(int $id): ?Website
    {
		return $this->defaultJoin($this->createQueryBuilder('w'))
			->andWhere('w.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
    }

    /**
     * Get Website as array
     *
     * @param int $id
     * @return array
     */
    public function findArray(int $id): array
	{
		$result = $this->defaultJoin($this->createQueryBuilder('w'))
			->andWhere('w.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
    }

	/**
	 * Get current Website
	 *
	 * @param bool $hasArray
	 * @return Website
	 * @throws NonUniqueResultException
	 */
    public function findCurrent(bool $hasArray = false)
    {
        $website = $this->findOneByHost(NULL, false, $hasArray);

        if (!$website) {
            $website = $this->findDefault($hasArray);
        }

        return $website;
    }

    /**
     * Get all Website[] as array
     *
     * @return Website[]
	 */
    public function findAllArray(): array
	{
		return $this->defaultJoin($this->createQueryBuilder('w'))
			->getQuery()
			->getArrayResult();
    }

	/**
	 * Get Website by Host name
	 *
	 * @param string|null $host
	 * @param bool $forceByHost
	 * @param bool $hasArray
	 * @return Website|array|null
	 * @throws NonUniqueResultException
	 */
    public function findOneByHost(string $host = NULL, bool $forceByHost = false, bool $hasArray = false)
    {
        $host = !empty($host) ? $host : $this->host;

		$queryBuilder = $this->defaultJoin($this->createQueryBuilder('w'))
			->andWhere('d.name = :host')
			->setParameter('host', $host)
			->getQuery();

        if($hasArray) {
			$result = $queryBuilder->getArrayResult();
			$result = !empty($result[0]) ? $result[0] : NULL;
		} else {
			$result = $queryBuilder->getOneOrNullResult();
		}

        if ($forceByHost && $result) {
            return $result;
        }

        return $result ?: $this->findDefault($hasArray);
    }

	/**
	 * Get default Website
	 *
	 * @param bool $hasArray
	 * @return Website|array|null
	 * @throws NonUniqueResultException
	 */
    public function findDefault(bool $hasArray = false)
    {
		$queryBuilder = $this->createQueryBuilder('w')
            ->leftJoin('w.configuration', 'c')
            ->andWhere('c.hasDefault = :hasDefault')
            ->setParameter('hasDefault', true)
            ->addSelect('c')
            ->getQuery();

		if($hasArray) {
			$result = $queryBuilder->getArrayResult();
			$result = !empty($result[0]) ? $result[0] : NULL;
		} else {
			$result = $queryBuilder->getOneOrNullResult();
		}

		return $result;
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
			->leftJoin('w.configuration', 'c')
			->leftJoin('w.api', 'a')
			->leftJoin('w.information', 'i')
			->leftJoin('w.security', 's')
			->leftJoin('w.seoConfiguration', 'sc')
			->leftJoin('c.domains', 'd')
			->leftJoin('c.transitions', 'ct')
			->leftJoin('c.modules', 'cm')
			->leftJoin('c.pages', 'cp')
			->addSelect('c')
			->addSelect('a')
			->addSelect('i')
			->addSelect('s')
			->addSelect('sc')
			->addSelect('d')
			->addSelect('ct')
			->addSelect('cm')
			->addSelect('cp');
    }
}
