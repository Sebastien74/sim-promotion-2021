<?php

namespace App\Repository\Information;

use App\Entity\Information\Information;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * InformationRepository
 *
 * @method Information|null find($id, $lockMode = null, $lockVersion = null)
 * @method Information|null findOneBy(array $criteria, array $orderBy = null)
 * @method Information[]    findAll()
 * @method Information[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationRepository extends ServiceEntityRepository
{
	/**
	 * InformationRepository constructor.
	 *
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Information::class);
	}

	/**
	 * Get Information as array
	 *
	 * @param int $id
	 * @return array
	 */
	public function findArray(int $id): array
	{
		$result = $this->createQueryBuilder('i')
			->leftJoin('i.i18ns', 'ii')
			->leftJoin('i.emails', 'ie')
			->leftJoin('i.addresses', 'ia')
			->leftJoin('ia.phones', 'iap')
			->leftJoin('ia.emails', 'iae')
			->leftJoin('i.phones', 'ip')
			->leftJoin('i.website', 'iw')
			->andWhere('i.id = :id')
			->setParameter('id', $id)
			->addSelect('ii')
			->addSelect('ie')
			->addSelect('ia')
			->addSelect('iap')
			->addSelect('iae')
			->addSelect('ip')
			->addSelect('iw')
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
	}
}
