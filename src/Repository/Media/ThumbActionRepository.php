<?php

namespace App\Repository\Media;

use App\Entity\Core\Website;
use App\Entity\Media\ThumbAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ThumbActionRepository
 *
 * @method ThumbAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThumbAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThumbAction[]    findAll()
 * @method ThumbAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbActionRepository extends ServiceEntityRepository
{
    /**
     * ThumbActionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThumbAction::class);
    }

    /**
     * Find by Website
     *
     * @param Website $website
     * @return array
     */
    public function findByWebsite(Website $website): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.configuration', 'c')
            ->leftJoin('c.configuration', 'cc')
            ->andWhere('c.configuration = :configuration')
            ->setParameter('configuration', $website->getConfiguration())
            ->addSelect('c')
            ->addSelect('cc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find for entity
     *
     * @param Website $website
     * @param string $namespace
     * @param string|null $action
     * @param int|string|null $filterId
     * @param string|null $filterBlock
     * @return array
     */
    public function findForEntity(Website $website, string $namespace, string $action = NULL, $filterId = NULL, string $filterBlock = NULL): array
	{
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.configuration', 'c')
            ->leftJoin('c.configuration', 'cc')
            ->andWhere('t.namespace = :namespace')
            ->andWhere('c.configuration = :configuration')
            ->setParameter('namespace', $namespace)
            ->setParameter('configuration', $website->getConfiguration())
            ->addSelect('c')
            ->addSelect('cc');

        if ($action) {
            $qb->andWhere('t.action = :action')
                ->setParameter('action', $action);
        }

        if ($filterId) {
            $qb->andWhere('t.actionFilter = :actionFilter')
                ->setParameter('actionFilter', $filterId);
        }

        if ($filterBlock) {
            $qb->leftJoin('t.blockType', 'bt')
                ->andWhere('bt.slug = :slug')
                ->setParameter('slug', $filterBlock)
                ->addSelect('bt');
        }

        $result = $qb->getQuery()
            ->getArrayResult();

        if (!$filterId) {
            foreach ($result as $thumb) {
                if (!$thumb['actionFilter']) {
                    return $thumb;
                }
            }
        }

        return $result ? $result[0] : [];
    }

    /**
     * Get thumb by namespace and filter
     *
     * @param Website $website
     * @param string $namespace
     * @param mixed $actionFilter
     * @return ThumbAction|null
     */
    public function findByNamespaceAndFilter(Website $website, string $namespace, $actionFilter): ?ThumbAction
    {
        $result = $this->createQueryBuilder('t')
            ->leftJoin('t.configuration', 'c')
            ->leftJoin('c.configuration', 'cc')
            ->andWhere('t.namespace = :namespace')
            ->andWhere('t.actionFilter = :actionFilter')
            ->andWhere('c.configuration = :configuration')
            ->setParameter('namespace', $namespace)
            ->setParameter('actionFilter', $actionFilter)
            ->setParameter('configuration', $website->getConfiguration())
            ->addSelect('c')
            ->addSelect('cc')
            ->getQuery()
            ->getResult();

        return $result ? $result[0] : NULL;
    }
}
