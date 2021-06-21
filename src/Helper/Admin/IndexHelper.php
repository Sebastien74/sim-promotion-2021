<?php

namespace App\Helper\Admin;

use App\Form\Manager\Core\SearchManager;
use App\Form\Type\Core\IndexSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * IndexHelper
 *
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property PaginatorInterface $paginator
 * @property SearchManager $searchManager
 * @property mixed $repository
 * @property FormFactoryInterface $formFactory
 * @property PaginatorInterface $pagination
 * @property mixed $entityConf
 * @property bool $displaySearchForm
 * @property Form|bool $searchForm
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IndexHelper
{
    private $request;
    private $entityManager;
    private $paginator;
    private $searchManager;
    private $repository;
    private $formFactory;
    private $pagination;
    private $entityConf;
    private $displaySearchForm = false;
    private $searchForm = false;

    /**
     * IndexHelper constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @param SearchManager $searchManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        SearchManager $searchManager,
        FormFactoryInterface $formFactory)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->searchManager = $searchManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Execute helper
     *
     * @param string $classname
     * @param array $interface
     * @param int|string|null $limit
     * @param object|null $entities
     * @param bool $forceEntities
     */
    public function execute(string $classname, array $interface, $limit = NULL, $entities = NULL, $forceEntities = false)
    {
        $this->setRepository($classname);
        $this->setEntityConf($interface);
        $this->setSearchForm($interface);
        $this->setPagination($interface, $limit, $entities, $forceEntities);
    }

    /**
     * Get entity Repository
     *
     * @return object
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set entity Repository
     *
     * @param string $classname
     */
    public function setRepository(string $classname): void
    {
        $this->repository = $this->entityManager->getRepository($classname);
    }

    /**
     * Get entity configuration
     *
     * @return object
     */
    public function getEntityConf()
    {
        return $this->entityConf;
    }

    /**
     * Set entity configuration
     *
     * @param array $interface
     */
    public function setEntityConf(array $interface): void
    {
        $this->entityConf = $interface['configuration'];
    }

    /**
     * Set is display searchForm
     * @param bool $display
     */
    public function setDisplaySearchForm(bool $display): void
    {
        $this->displaySearchForm = $display;
    }

    /**
     * Get search Form
     *
     * @return Form|bool
     */
    public function getSearchForm()
    {
        return $this->searchForm;
    }

    /**
     * Set search Form
     *
     * @param array $interface
     */
    public function setSearchForm(array $interface): void
    {
        if ($this->displaySearchForm) {
            $this->searchForm = $this->formFactory->createBuilder(IndexSearchType::class, NULL, ['interface' => $interface])
                ->setMethod('GET')
                ->getForm();
        }
    }

    /**
     * @return mixed
     *
     * @return SlidingPagination
     */
    public function getPagination(): SlidingPagination
    {
        return $this->pagination;
    }

    /**
     * Set pagination
     *
     * @param array $interface
     * @param int|string $limit
     * @param mixed|null $entities
     * @param bool $forceEntities
     */
    public function setPagination(array $interface, $limit, $entities = NULL, bool $forceEntities = false): void
    {
        $queryLimit = $limit ? $limit : $this->entityConf->adminLimit;
        $queryLimit = $limit === "all" ? 100000000 : $queryLimit;

        if ($this->displaySearchForm) {
            $this->searchForm->handleRequest($this->request);
        }

        $queryBuilder = $this->getQueryBuilder($interface, $entities, $forceEntities);

        $this->pagination = $this->paginator->paginate(
            $queryBuilder,
            $this->request->query->getInt('page', 1),
            $queryLimit,
            ['wrap-queries' => true]
        );
    }

    /**
     * Get QueryBuider
     *
     * @param array $interface
     * @param null $entities
     * @param $forceEntities
     * @return Query|QueryBuilder|null
     */
    private function getQueryBuilder(array $interface, $entities = NULL, $forceEntities = false)
    {
        $queryBuilder = NULL;

        if ($this->displaySearchForm && $this->searchForm->isSubmitted() && !empty($this->searchForm->getData()['search'])) {
            $queryBuilder = $this->searchManager->execute($this->searchForm, $interface);
        }

        if (!$queryBuilder) {

            if ($entities || $forceEntities) {
                $queryBuilder = $entities;
            } elseif (!empty($interface['entity']) && method_exists($interface['entity'], 'getUrls')) {

                $params = $this->getQueryParams($interface);

                /** @var QueryBuilder $queryBuilder */
                $queryBuilder = $this->repository->createQueryBuilder('e')
                    ->leftJoin('e.urls', 'u')
                    ->andWhere('u.isArchived = :isArchived')
                    ->setParameter('isArchived', false)
                    ->orderBy('e.' . $this->entityConf->orderBy, $this->entityConf->orderSort);

                foreach ($params as $name => $param) {
                    $queryBuilder->andWhere('e.' . $name . ' = :' . $name);
                    $queryBuilder->setParameter($name, $param);
                }
            } elseif (is_object($interface['entity']) && property_exists($interface['entity'], $this->entityConf->orderBy)
                && is_object($this->searchForm) && !$this->searchForm->isSubmitted()) {
                $queryBuilder = $this->repository->findBy($this->getQueryParams($interface), [$this->entityConf->orderBy => $this->entityConf->orderSort]);
            } else {
                $orderBy = is_object($this->entityConf) && method_exists($this->entityConf, 'orderBy') ? $this->entityConf->orderBy : 'position';
                $properties = explode('.', $orderBy);
                $params = $this->getQueryParams($interface);
                if (count($properties) == 2) {
                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $this->repository->createQueryBuilder('e');
                    foreach ($params as $property => $value) {
                        if($value) {
                            $queryBuilder->andWhere('e.' . $property . ' = :' . $property)
                                ->setParameter($property, $value);
                        }
                    }
                    $orderSort = is_object($this->entityConf) && method_exists($this->entityConf, 'orderSort') ? $this->entityConf->orderSort : 'ASC';
                    $queryBuilder->leftJoin('e.' . $properties[0], 'j')
                        ->orderBy('j.' . $properties[1], $orderSort);
                } else {
                    $orderBy = $this->entityConf && method_exists($interface['entity'], 'get' . ucfirst($this->entityConf->orderBy))
                        ? [$this->entityConf->orderBy => $this->entityConf->orderSort] : [];
                    $queryBuilder = $this->repository->findBy($params, $orderBy);
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * Get Query Params
     *
     * @param array $interface
     * @return array
     */
    private function getQueryParams(array $interface): array
    {
        if (!empty($interface['masterField']) && !empty($interface['parentMasterField'])) {
            return [
                $interface['masterField'] => $this->request->get($interface['masterField']),
                $interface['parentMasterField'] => $this->request->get($interface['parentMasterField'])
            ];
        } elseif (!empty($interface['masterField']) && $interface['masterField'] === 'website') {
            return [
                'website' => $interface['website']
            ];
        } elseif (!empty($interface['masterField']) && $interface['masterField'] === 'configuration') {
            return [
                'configuration' => $interface['website']->getConfiguration()
            ];
        } elseif (!empty($interface['masterField']) && !empty($this->request->get($interface['masterField']))) {
            return [
                $interface['masterField'] => $this->request->get($interface['masterField'])
            ];
        }

        return [];
    }
}