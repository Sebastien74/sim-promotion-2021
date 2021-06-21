<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * SearchFilterService
 *
 * Manage filter search
 *
 * @property EntityManagerInterface $entityManager
 * @property FilterBuilderUpdaterInterface $builderUpdater
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchFilterService
{
    private $entityManager;
    private $builderUpdater;

    /**
     * SearchFilterService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FilterBuilderUpdaterInterface $builderUpdater
     */
    public function __construct(EntityManagerInterface $entityManager, FilterBuilderUpdaterInterface $builderUpdater)
    {
        $this->entityManager = $entityManager;
        $this->builderUpdater = $builderUpdater;
    }

    /**
     * Execute search filter process
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $interface
     * @return array
     */
    public function execute(Request $request, FormInterface $form, array $interface)
    {
        $repository = $this->entityManager->getRepository($interface['classname']);
        /** @var QueryBuilder $filterBuilder */

        $filterBuilder = $repository->createQueryBuilder('e');
        if (!empty($interface['masterField']) && $request->get($interface['masterField'])) {
            $filterBuilder->andWhere('e.' . $interface['masterField'] . ' = :' . $interface['masterField']);
            $filterBuilder->setParameter($interface['masterField'], $request->get($interface['masterField']));
        }

        $this->builderUpdater->addFilterConditions($form, $filterBuilder);

        return $filterBuilder->getQuery()->getResult();
    }
}