<?php

namespace App\Repository\Core;

use App\Entity\Core\Color;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ColorRepository
 *
 * @method Color|null find($id, $lockMode = null, $lockVersion = null)
 * @method Color|null findOneBy(array $criteria, array $orderBy = null)
 * @method Color[]    findAll()
 * @method Color[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColorRepository extends ServiceEntityRepository
{
    /**
     * ColorRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Color::class);
    }

	/**
	 * Get Color[] as array
	 *
	 * @param int $configurationId
	 * @return array
	 */
	public function findByConfiguration(int $configurationId): array
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.configuration = :configuration')
			->setParameter('configuration', $configurationId)
			->getQuery()
			->getArrayResult();
	}
}
