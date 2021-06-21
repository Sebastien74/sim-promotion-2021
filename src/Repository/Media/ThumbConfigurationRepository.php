<?php

namespace App\Repository\Media;

use App\Entity\Core\Configuration;
use App\Entity\Layout\Block;
use App\Entity\Media\ThumbConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ThumbConfigurationRepository
 *
 * @method ThumbConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThumbConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThumbConfiguration[]    findAll()
 * @method ThumbConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbConfigurationRepository extends ServiceEntityRepository
{
    /**
     * ThumbConfigurationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThumbConfiguration::class);
    }

    /**
     * Get ThumbConfiguration by namespaces[]
     *
     * @param array $namespaces
     * @param Configuration $configuration
     * @return ThumbConfiguration[]
     */
    public function findByNamespaces(array $namespaces, Configuration $configuration)
    {
        $thumbConfigurations = [];

        if (!$namespaces) {
            return $thumbConfigurations;
        }

        foreach ($namespaces as $key => $namespace) {

            $statement = $this->createQueryBuilder('t')
                ->leftJoin('t.actions', 'a')
                ->leftJoin('t.configuration', 'c')
                ->andWhere('t.configuration = :configuration')
                ->andWhere('a.namespace = :namespace')
                ->setParameter('configuration', $configuration)
                ->setParameter('namespace', $namespace['classname'])
                ->addSelect('c')
                ->addSelect('a');

            if ($namespace['entity'] instanceof Block) {
                $statement->andWhere('a.blockType = :blockType');
                $statement->setParameter('blockType', $namespace['entity']->getBlockType());
            }

            $result = $statement->orderBy('t.height', 'DESC')
                ->getQuery()
                ->getResult();

            if ($result) {
                foreach ($result as $item) {
                    $thumbConfigurations[] = $item;
                }
            }
        }

        $results = [];
        foreach ($thumbConfigurations as $key => $thumbConfiguration) {
            /** @var ThumbConfiguration $thumbConfiguration */
            if (empty($results[$thumbConfiguration->getId()])) {
                $results[$thumbConfiguration->getId()] = $thumbConfiguration;
            }
        }

        return $results;
    }
}