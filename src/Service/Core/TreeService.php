<?php

namespace App\Service\Core;

use App\Entity\Seo\Url;
use Doctrine\ORM\EntityManagerInterface;

/**
 * TreeService
 *
 * To generate tree array of entity[]
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TreeService
{
    private $entityManager;

    /**
     * TreeService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Execute tree generator
     *
     * @param array|object $entities
     * @return array
     */
    public function execute($entities): array
    {
        $tree = [];
        $positions = [];

        foreach ($entities as $entity) {

            $isConfigObject = is_object($entity) && property_exists($entity, 'entity');
            $configObject = $isConfigObject ? $entity : NULL;
            $entity = $isConfigObject ? $entity->entity : $entity;
            $push = true;

            if (is_object($entity) && method_exists($entity, 'getUrls')) {
                foreach ($entity->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url->getIsArchived()) {
                        $push = false;
                    }
                }
            }

            if ($push) {

                $parent = $this->getParent($entity);
                $position = $this->getPosition($entity);
                $setPosition = !empty($positions[$parent]) && in_array($position, $positions[$parent]);
                $position = $setPosition ? count($positions[$parent]) + 1 : $position;
                $positions[$parent][] = $position;
                $tree[$parent][$position] = $isConfigObject ? $configObject : $entity;
                ksort($tree[$parent]);

                if (is_object($entity) && $setPosition) {
                    $entity->setPosition($position);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($entity);
                }
            }
        }

        return $tree;
    }

    private function getParent($entity)
    {
        return is_array($entity) && !empty($entity['parent']) ? $entity['parent']['id'] : (is_object($entity) && $entity->getParent() ? $entity->getParent()->getId() : 'main');
    }

    private function getPosition($entity)
    {
        return is_array($entity) ? $entity['position'] : $entity->getPosition();
    }
}