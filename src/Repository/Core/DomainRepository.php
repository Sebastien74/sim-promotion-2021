<?php

namespace App\Repository\Core;

use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * DomainRepository
 *
 * @method Domain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domain[]    findAll()
 * @method Domain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainRepository extends ServiceEntityRepository
{
    /**
     * DomainRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

	/**
	 * Find by name
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function findByNameArray(string $name): ?array
	{
		$result = $this->createQueryBuilder('d')
			->andWhere('d.name = :name')
			->setParameter('name', $name)
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : NULL;
	}

	/**
	 * Find by name
	 *
	 * @param Configuration $configuration
	 * @param string $locale
	 * @return array|null
	 */
	public function findDefaultByConfigurationAndLocaleArray(Configuration $configuration, string $locale): ?array
	{
		$result = $this->createQueryBuilder('d')
			->andWhere('d.configuration = :configuration')
			->andWhere('d.locale = :locale')
			->andWhere('d.hasDefault = :hasDefault')
			->setParameter('configuration', $configuration)
			->setParameter('locale', $locale)
			->setParameter('hasDefault', true)
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : NULL;
	}
}
