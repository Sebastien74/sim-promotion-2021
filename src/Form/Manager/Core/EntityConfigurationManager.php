<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;

/**
 * EntityConfigurationManager
 *
 * Manage admin Entity configuration form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityConfigurationManager
{
    private $entityManager;

    /**
     * EntityConfigurationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param Entity $entity
     * @param Website $website
     */
    public function prePersist(Entity $entity, Website $website)
    {
        $this->setClassName($entity);
        $this->entityManager->persist($entity);
    }

    /**
     * @preUpdate
     *
     * @param Entity $entity
     * @param Website $website
     */
    public function preUpdate(Entity $entity, Website $website)
    {
        $this->setClassName($entity);

        $this->entityManager->persist($entity);
    }

    /**
     * Set Entity classname
     *
     * @param Entity $entity
     */
    private function setClassName(Entity $entity)
    {
        $entity->setClassName(str_replace('/', '\\', $entity->getClassName()));
    }
}