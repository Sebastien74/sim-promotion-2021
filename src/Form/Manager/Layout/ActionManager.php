<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Action;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ActionManager
 *
 * Manage admin Action configuration form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionManager
{
    private $entityManager;

    /**
     * ActionManager constructor.
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
     * @param Action $action
     * @param Website $website
     */
    public function prePersist(Action $action, Website $website)
    {
        $this->setEntity($action);
        $this->entityManager->persist($action);
    }

    /**
     * @preUpdate
     *
     * @param Action $action
     * @param Website $website
     */
    public function preUpdate(Action $action, Website $website)
    {
        $this->setEntity($action);
        $this->entityManager->persist($action);
    }

    /**
     * Set Entity classname
     *
     * @param Action $action
     */
    private function setEntity(Action $action)
    {
        $action->setEntity(str_replace('/', '\\', $action->getEntity()));
    }
}