<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\LayoutConfiguration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * LayoutConfigurationManager
 *
 * Manage admin Layout configuration form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutConfigurationManager
{
    private $entityManager;

    /**
     * LayoutConfigurationManager constructor.
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
     * @param LayoutConfiguration $layoutConfiguration
     * @param Website $website
     */
    public function prePersist(LayoutConfiguration $layoutConfiguration, Website $website)
    {
        $this->setEntity($layoutConfiguration);
        $this->entityManager->persist($layoutConfiguration);
    }

    /**
     * @preUpdate
     *
     * @param LayoutConfiguration $layoutConfiguration
     * @param Website $website
     */
    public function preUpdate(LayoutConfiguration $layoutConfiguration, Website $website)
    {
        $this->setEntity($layoutConfiguration);
        $this->entityManager->persist($layoutConfiguration);
    }

    /**
     * Set LayoutConfiguration classname
     *
     * @param LayoutConfiguration $layoutConfiguration
     */
    private function setEntity(LayoutConfiguration $layoutConfiguration)
    {
        $layoutConfiguration->setEntity(str_replace('/', '\\', $layoutConfiguration->getEntity()));
    }
}