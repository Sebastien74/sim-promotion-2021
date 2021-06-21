<?php

namespace App\Helper\Admin;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * TreeHelper
 *
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property QueryBuilder $queryBuilder
 * @property mixed $baseEntity
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TreeHelper
{
    private $request;
    private $entityManager;
    private $queryBuilder;
    private $baseEntity;

    /**
     * TreeHelper constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->queryBuilder = $entityManager->createQueryBuilder();
    }

    /**
     * Get entities for tree
     *
     * @param string $classname
     * @param array $interface
     * @param Website $website
     * @return mixed|object[]
     */
    public function execute(string $classname, array $interface, Website $website)
    {
        $this->baseEntity = new $classname();

        if (method_exists($this->baseEntity, 'getUrls')) {
            return $this->getByUrls($classname, $website);
        } elseif (!empty($interface['masterField'])) {
            return $this->getByMasterField($classname, $interface);
        } else {
            return $this->getEntities($classname);
        }
    }

    /**
     * Get by URL
     *
     * @param string $classname
     * @param Website $website
     * @return mixed
     */
    private function getByUrls(string $classname, Website $website)
    {
        $queryBuilder = $this->queryBuilder->select('e')
            ->from($classname, 'e')
            ->leftJoin('e.urls', 'u')
            ->andWhere('u.isArchived = :isArchived')
            ->andWhere('u.website = :website')
            ->setParameter('isArchived', false)
            ->setParameter('website', $website)
            ->addSelect('u');

        if ($this->baseEntity instanceof Page) {
            $queryBuilder->andWhere('e.deletable = :deletable')
                ->setParameter('deletable', true);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get by masterFiled
     *
     * @param string $classname
     * @param array $interface
     * @return object[]
     */
    private function getByMasterField(string $classname, array $interface)
    {
        return $this->entityManager->getRepository($classname)
            ->findBy([
                $interface['masterField'] => $interface['masterFieldId']
            ], ['position' => 'ASC']);
    }

    /**
     * Find All
     *
     * @param string $classname
     * @return object[]
     */
    private function getEntities(string $classname)
    {
        return $this->entityManager->getRepository($classname)->findBy([], ['position' => 'ASC']);
    }
}