<?php

namespace App\Repository\Core;

use App\Entity\Core\Configuration;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ConfigurationRepository
 *
 * @method Configuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Configuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Configuration[]    findAll()
 * @method Configuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationRepository extends ServiceEntityRepository
{
    /**
     * ConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Configuration::class);
    }

    /**
     * Get Configuration optimized query
     *
     * @param Website $website
     * @return Configuration
     * @throws NonUniqueResultException
     */
    public function findOptimizedAdmin(Website $website)
    {
        return $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.website', 'w')
            ->andWhere('w.id = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get MediaRelation[] Configuration
     *
     * @param int $configurationId
     * @return array|PersistentCollection
     * @throws NonUniqueResultException
     */
    public function findMediaRelations(int $configurationId)
    {
        $configuration = $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.mediaRelations', 'mr')
            ->leftJoin('mr.media', 'm')
            ->andWhere('c.id = :id')
            ->andWhere('mr.media IS NOT NULL')
            ->setParameter('id', $configurationId)
            ->addSelect('mr')
            ->addSelect('m')
            ->getQuery()
            ->getOneOrNullResult();

        return $configuration ? $configuration->getMediaRelations() : [];
    }

    /**
     * Get BlockTypes by categories
     *
     * @param Website $website
     * @param $categories
     * @return array|PersistentCollection
     * @throws NonUniqueResultException
     */
    public function findBlocksTypes(Website $website, $categories)
    {
        $qb = $this->createQueryBuilder('c')->select('c')
            ->join('c.blockTypes', 'b')
            ->join('c.website', 'w')
            ->andWhere('w.id = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('b');

        foreach ($categories as $key => $category) {
            $qb->orWhere('b.category = :category' . $key);
            $qb->setParameter(':category' . $key, $category);
        }

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ? $result->getBlockTypes() : [];
    }

    /**
     * Check if Website Module existing
     *
     * @param Website $website
     * @param Module|null $module
     * @param bool $object
     * @return bool|Module
     */
    public function moduleExist(Website $website, Module $module = NULL, bool $object = false)
    {
        if (!$module) {
            return false;
        }

        foreach ($website->getConfiguration()->getModules() as $moduleDb) {
            if ($moduleDb->getId() === $module->getId()) {
                return $object ? $module : true;
            }
        }
    }
}
