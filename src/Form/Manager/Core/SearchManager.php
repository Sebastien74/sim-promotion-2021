<?php

namespace App\Form\Manager\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SearchManager
 *
 * Manage admin search in index
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property array $interface
 * @property string|null $masterField
 * @property mixed $entity
 * @property array $fields
 * @property QueryBuilder $queryBuilder
 * @property array $alias
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchManager
{
    private $entityManager;
    private $request;
    private $interface = [];
    private $masterField;
    private $entity;
    private $fields = [];
    private $queryBuilder;
    private $alias = [];

    /**
     * SearchManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Get form search QueryBuilder
     *
     * @param Form $form
     * @param array $interface
     * @return Query
     */
    public function execute(Form $form, array $interface)
    {
        $this->setInterface($interface);
        $this->setMasterField();
        $this->setEntity();
        $this->setFields();

        $searchValue = $form->getData()['search'];
        $classname = $this->interface['classname'];
        $referClass = new $classname();

        $this->queryBuilder = $this->entityManager->getRepository($this->interface['classname'])->createQueryBuilder('e');

        if (!empty($searchValue)) {

            foreach ($this->fields as $field) {
                $fieldName = !empty($field['meta']['fieldName']) ? $field['meta']['fieldName'] : NULL;
                if (!empty($fieldName) && is_object($this->entity) && method_exists($this->entity, 'get' . ucfirst($fieldName))) {
                    $this->setQuery($field, $searchValue);
                }
            }

            if (!empty($this->masterField) && !empty($this->request->get($this->masterField))) {
                $this->queryBuilder->andWhere('e.' . $this->masterField . ' = :' . $this->masterField);
                $this->queryBuilder->setParameter($this->masterField, $this->request->get($this->masterField));
            }

            if (is_object($referClass) && method_exists($referClass, 'getUrls')) {
                $this->queryBuilder->leftJoin('e.urls', 'u');
                $this->queryBuilder->andWhere('u.isArchived = :isArchived');
                $this->queryBuilder->setParameter('isArchived', false);
                $this->queryBuilder->addSelect('u');
            }
        }

        return $this->queryBuilder->getQuery();
    }

    /**
     * Set interface
     *
     * @param array $interface
     */
    private function setInterface(array $interface): void
    {
        $this->interface = $interface;
    }

    /**
     * Set masterField
     */
    private function setMasterField(): void
    {
        $this->masterField = !empty($this->interface['masterField']) ? $this->interface['masterField'] : NULL;
    }

    /**
     * Set entity
     */
    private function setEntity(): void
    {
        $this->entity = $this->interface['entity'];
    }

    /**
     * Set fields
     */
    private function setFields(): void
    {
        $metaData = $this->entityManager->getClassMetadata($this->interface['classname']);
        $fields = !empty($this->interface['configuration'])
        && property_exists($this->interface['configuration'], 'searchFields')
        && $this->interface['configuration']->searchFields
            ? $this->interface['configuration']->searchFields : ['adminName'];

        foreach ($fields as $field) {

            try {

                $matches = [];
                $fieldName = $field;
                if (preg_match('/./', $field)) {
                    $matches = explode('.', $field);
                    $fieldName = $matches[0];
                }

                if (!empty($metaData->getAssociationMappings()[$fieldName])) {
                    $this->fields[] = [
                        'meta' => $metaData->getAssociationMappings()[$fieldName],
                        'joinProperty' => !empty($matches[1]) ? $matches[1] : 'adminName'
                    ];
                } elseif (!empty($metaData->getFieldMapping($fieldName))) {
                    $this->fields[] = [
                        'meta' => $metaData->getFieldMapping($fieldName)
                    ];
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        $excludeTypes = ['datetime', 'boolean'];
        foreach ($this->fields as $key => $field) {
            if (in_array($field['meta']['type'], $excludeTypes)) {
                unset($this->fields[$key]);
            }
        }
    }

    /**
     * Set Query
     *
     * @param array $field
     * @param string $searchValue
     */
    private function setQuery(array $field, string $searchValue)
    {
        $fieldName = $field['meta']['fieldName'];
        $alias = substr(str_shuffle($fieldName) . uniqid(), 0, 5);

        if ($field['meta']['type'] === 'string' || $field['meta']['type'] === 'text') {
            $this->queryBuilder->orWhere('e.' . $fieldName . ' LIKE :' . $fieldName);
            $this->queryBuilder->setParameter($fieldName, '%' . $searchValue . '%');
        } elseif (!empty($field['joinProperty']) && !in_array($alias, $this->alias)) {
            $targetEntity = new $field['meta']['targetEntity'];
            $joinProperty = $field['joinProperty'];
            if (method_exists($targetEntity, 'get' . ucfirst($joinProperty))) {
                $this->queryBuilder->leftJoin('e.' . $fieldName, $alias);
                $this->queryBuilder->orWhere($alias . '.' . $joinProperty . ' LIKE :' . $fieldName);
                $this->queryBuilder->setParameter($fieldName, '%' . $searchValue . '%');
                $this->queryBuilder->addSelect($alias);
                $this->alias[] = $this->alias;
            }
        }
    }
}