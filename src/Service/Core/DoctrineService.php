<?php

namespace App\Service\Core;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DoctrineService
 *
 * Manage Doctrine
 *
 * @property ContainerInterface $container
 * @property Registry $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DoctrineService
{
    private $container;
    private $entityManager;

    /**
     * DoctrineService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $this->container->get('doctrine');
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function directFetchAll(string $sql)
    {
        $entityManager = $this->entityManager->getManager('direct');
        $conn = $entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAllAssociativeIndexed();
    }
}