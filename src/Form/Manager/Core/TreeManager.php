<?php

namespace App\Form\Manager\Core;

use Doctrine\ORM\EntityManagerInterface;

/**
 * TreeManager
 *
 * Manage Entities in tree in admin
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TreeManager
{
    private $entityManager;

    /**
     * TreeManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set positions of tree elements
     *
     * @param array $data
     * @param string $classname
     */
    public function post(array $data, string $classname)
    {
        $outputs = json_decode($data['nestable_output']);
        $this->setPositionsByLevel($classname, $outputs, 1, NULL);
    }

    /**
     * Set positions of tree elements by level
     *
     * @param string $classname
     * @param array $outputs
     * @param int $level
     * @param mixed|null $parent
     */
    private function setPositionsByLevel(string $classname, array $outputs, int $level, $parent = NULL)
    {
        $repository = $this->entityManager->getRepository($classname);
        $position = 1;

        foreach ($outputs as $output) {

            $entity = $repository->find($output->id);

            if (!empty($entity)) {

                $entity->setPosition($position);

                if (method_exists($entity, 'setLevel')) {
                    $entity->setLevel($level);
                }

                if (method_exists($entity, 'setParent')) {
                    $entity->setParent($parent);
                }

                $this->entityManager->persist($entity);
                $this->entityManager->flush();

                if (property_exists($output, 'children') && !empty($output->children)) {
                    $this->setPositionsByLevel($classname, $output->children, $level + 1, $entity);
                }

                $position++;
            }
        }
    }
}