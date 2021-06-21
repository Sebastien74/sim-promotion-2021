<?php

namespace App\Repository\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Redirection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * RedirectionRepository
 *
 * @method Redirection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Redirection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Redirection[]    findAll()
 * @method Redirection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RedirectionRepository extends ServiceEntityRepository
{
    /**
     * RedirectionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirection::class);
    }

	/**
	 * Find redirection for front
	 *
	 * @param Request $request
	 * @return Redirection[]
	 */
	public function findForFront(Request $request): array
	{
		return $this->createQueryBuilder('r')
			->leftJoin('r.website', 'w')
			->andWhere('r.old IN (:old)')
			->setParameter('old', [$request->getUri(), $request->getRequestUri()])
            ->addSelect('w')
			->getQuery()
			->getArrayResult();
	}
}
