<?php

namespace App\Service\Admin;

use App\Helper\Admin\IndexHelper;
use App\Helper\Core\InterfaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * PositionService
 *
 * Manage entity position
 *
 * @property EntityManagerInterface $entityManager
 * @property InterfaceHelper $interfaceHelper
 * @property IndexHelper $indexHelper
 * @property array $interface
 * @property Request $request
 * @property mixed $repository
 * @property mixed $entity
 * @property array $entities
 * @property int $count
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PositionService
{
    private $entityManager;
    private $interfaceHelper;
    private $indexHelper;
    private $interface;
    private $request;
    private $repository;
    private $entity;
    private $entities;
    private $count;

    /**
     * PositionService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param InterfaceHelper $interfaceHelper
     * @param IndexHelper $indexHelper
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        InterfaceHelper $interfaceHelper,
        IndexHelper $indexHelper)
    {
        $this->entityManager = $entityManager;
        $this->interfaceHelper = $interfaceHelper;
        $this->indexHelper = $indexHelper;
    }

    /**
     * Set Services vars
     *
     * @param string $classname
     * @param Request $request
     */
    public function setVars(string $classname, Request $request)
    {
        $this->setInterface($classname);
        $this->setRequest($request);
        $this->setRepository($classname);
        $this->setEntity();
        $this->setEntities($classname);
    }

    /**
     * Set entity position
     *
     * @param Form $form
     * @param mixed $postEntity
     */
    public function execute(Form $form, $postEntity)
    {
        $oldPosition = $form->getConfig()->getOption('old_position');
        $newPosition = $form->getData()->getPosition();
        $start = $newPosition < $oldPosition ? $newPosition : $oldPosition + 1;
        $end = $newPosition < $oldPosition ? $oldPosition - 1 : $newPosition;
        $type = $newPosition < $oldPosition ? 'up' : 'down';

        foreach ($this->entities as $entity) {
            if ($entity !== $postEntity) {
                $position = $entity->getPosition();
                if ($position >= $start && $position <= $end) {
                    $newPosition = $type === 'up' ? $position + 1 : $position - 1;
                    $entity->setPosition($newPosition);
                }
                $this->entityManager->persist($entity);
            }
        }

        $this->entityManager->persist($postEntity);
        $this->entityManager->flush();
    }

    /**
     * Get Interface
     *
     * @return array|null
     */
    public function getInterface(): ?array
	{
        return $this->interface;
    }

    /**
     * Set Interface
     *
     * @param string $classname
     */
    public function setInterface(string $classname): void
    {
        $this->interface = $this->interfaceHelper->generate($classname);
    }

    /**
     * Set Request
     *
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Set Entity
     *
     * @param string $classname
     */
    public function setRepository(string $classname): void
    {
        $this->repository = $this->entityManager->getRepository($classname);
    }

    /**
     * Get Entity
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set Entity
     */
    public function setEntity(): void
    {
        $this->entity = $this->repository->find($this->request->get($this->interface['name']));
    }

    /**
     * Get Entity[]
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Set Entities
     *
     * @param string $classname
     */
    public function setEntities(string $classname): void
    {
        $this->indexHelper->setDisplaySearchForm(false);
        $this->indexHelper->execute($classname, $this->interface, 'all');
        $pagination = $this->indexHelper->getPagination();

        if ($pagination instanceof SlidingPagination) {
            $this->entities = $pagination->getItems();
            $this->count = $pagination->getTotalItemCount();
        } elseif (is_array($pagination)) {
            $this->entities = $pagination;
            $this->count = count($this->entities);
        } else {
            $this->entities = [];
        }
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}