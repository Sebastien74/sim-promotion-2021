<?php

namespace App\Controller\Admin;

use App\Entity\Core\Website;
use App\Entity\Layout\LayoutConfiguration;
use App\Form\Manager\Core\TreeManager;
use App\Form\Manager\Layout\LayoutManager;
use App\Form\Manager\Seo\UrlManager;
use App\Form\Type\Core\FilterType;
use App\Form\Type\Core\PositionType;
use App\Form\Type\Core\TreeType;
use App\Helper\Admin\FormDuplicateHelper;
use App\Helper\Admin\TreeHelper;
use App\Service\Admin\DeleteService;
use App\Controller\BaseController;
use App\Helper\Admin\FormHelper;
use App\Helper\Admin\IndexHelper;
use App\Service\Admin\PositionService;
use App\Service\Admin\SearchFilterService;
use App\Service\Core\CacheService;
use App\Service\Export\ExportCsvService;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminController
 *
 * Admin base controller
 *
 * @property string $class
 * @property bool $forceEntities
 * @property array $entities
 * @property mixed $entity
 * @property string $pageTitle
 * @property string $formType
 * @property string $formManager
 * @property string $deleteService
 * @property string $exportService
 * @property string $formDuplicateManager
 * @property array $formOptions
 * @property string $template
 * @property string $templateConfig
 * @property bool $disableFormNew
 * @property bool $disableFlash
 * @property array $arguments
 * @property array $blockTypesCategories
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AdminController extends BaseController
{
    protected $class;
    protected $forceEntities = false;
    protected $entities = [];
    protected $entity;
    protected $pageTitle;
    protected $formType;
    protected $formManager;
    protected $deleteService;
    protected $exportService;
    protected $formDuplicateManager;
    protected $formOptions = [];
    protected $template;
    protected $templateConfig;
    protected $disableFormNew = false;
    protected $disableFlash = false;
    protected $arguments = [];

    /**
     * Index view
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    protected function index(Request $request)
    {
        $this->disableProfiler();
        $interface = $this->getInterface($this->class);
        $website = !empty($interface['website']) && $interface['website'] instanceof Website ? $interface['website'] : $this->getWebsite($request);

        $filterForm = is_object($interface['configuration']) && $interface['configuration']->searchFilters
            ? $this->createForm(FilterType::class, NULL, [
                'method' => 'GET',
                'filterName' => 'searchFilters',
                'website' => $website,
                'interface' => $interface
            ]) : NULL;

        $helper = $this->subscriber->get(IndexHelper::class);
        $helper->setDisplaySearchForm(true);
        $helper->execute($this->class, $interface, 10, $this->entities, $this->forceEntities);
        $pagination = $helper->getPagination();

        if ($filterForm) {
            $filterForm->handleRequest($request);
            if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                $pagination = $this->getPagination($this->subscriber->get(SearchFilterService::class)->execute($request, $filterForm, $interface), $interface['configuration']->adminLimit);
            }
        }

        $template = $this->template ? $this->template : 'admin/core/index.html.twig';
        $arguments = array_merge($this->arguments, [
            'disableFormNew' => $this->disableFormNew,
            'pageTitle' => $this->pageTitle,
            'namespace' => $this->getCurrentNamespace($request),
            'searchFiltersForm' => $filterForm ? $filterForm->createView() : NULL,
            'searchForm' => $helper->getSearchForm()->createView(),
            'columns' => $interface['configuration']->columns,
            'website' => $website,
            'pagination' => $pagination,
            'interface' => $interface
        ]);

        if (!empty($request->get('ajax'))) {
            return new JsonResponse(['html' => $this->cache($template, $arguments, $request)]);
        }

        return $this->cache($template, $arguments);
    }

    /**
     * Tree view
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    protected function tree(Request $request)
    {
        $this->disableProfiler();
        $interface = $this->getInterface($this->class);
        $template = !empty($this->template) ? $this->template : 'admin/core/tree.html.twig';
        $helper = $this->subscriber->get(TreeHelper::class);
        $entities = $helper->execute($this->class, $interface, $this->getWebsite($request));

        $formPositions = $this->getTreeForm($request);
        if ($formPositions instanceof JsonResponse) {
            return $formPositions;
        }

        return $this->cache($template, [
            'pageTitle' => $this->pageTitle,
            'tree' => $this->getTree($entities),
            'namespace' => $this->getCurrentNamespace($request),
            'interface' => $interface,
            'formPositions' => $formPositions->createView()
        ]);
    }

    /**
     * Set Tree Form position
     *
     * @param Request $request
     * @param string|null $classname
     * @return FormInterface|JsonResponse
     */
    protected function getTreeForm(Request $request, string $classname = NULL)
    {
        $formPositions = $this->createForm(TreeType::class);
        $formPositions->handleRequest($request);

        if ($formPositions->isSubmitted() && $formPositions->isValid()) {
            $data = $formPositions->getData();
            $class = $classname ? $classname : $this->class;
            $manager = $this->subscriber->get(TreeManager::class);
            $manager->post($data, $class);
            return new JsonResponse(['success' => true, 'data' => $data]);
        }

        return $formPositions;
    }

    /**
     * Layout view
     *
     * @param Request $request
     * @return Response
     */
    protected function layout(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_EDIT');
        $this->disableProfiler();

        return $this->forward(
            'App\Controller\Admin\AdminController::edition',
            $this->editionArguments($request)
        );
    }

    /**
     * New view
     *
     * @param Request $request
     * @return Response
     */
    protected function new(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_EDIT');

        return $this->forward(
            'App\Controller\Admin\AdminController::edition',
            $this->editionArguments($request)
        );
    }

    /**
     * Edit view
     *
     * @param Request $request
     * @return Response
     */
    protected function edit(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_EDIT');

        return $this->forward(
            'App\Controller\Admin\AdminController::edition',
            $this->editionArguments($request)
        );
    }

    /**
     * Show view
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    protected function show(Request $request)
    {
        $interface = $this->getInterface($this->class);
        $entity = $this->entityManager->getRepository($this->class)->find($request->get($interface['name']));
        $template = $this->template ? $this->template : 'admin/core/show.html.twig';

        if (!$entity) {
            throw $this->createNotFoundException(sprintf("Aucune entité pour l'ID %s", $request->get($interface['name'])));
        }

        return $this->cache($template, [
            'pageTitle' => $this->pageTitle,
            'entity' => $entity,
            'interface' => $interface,
            'metadata' => $this->entityManager->getClassMetadata($this->class)
        ]);
    }

    /**
     * Duplicate
     *
     * @param Request $request
     * @return Response
     */
    protected function duplicate(Request $request)
    {
        $formHelper = $this->subscriber->get(FormDuplicateHelper::class);
        $formHelper->execute($request, $this->formType, $this->class, $this->formOptions, $this->formDuplicateManager);

        if (!$formHelper->getEntity()) {
            return new Response();
        }

        $render = $this->renderView('admin/core/duplicate.html.twig', [
            'pageTitle' => $this->pageTitle,
            'form' => $formHelper->getForm()->createView(),
            'interface' => $formHelper->getInterface(),
            'entity' => $formHelper->getEntityToDuplicate(),
            'refresh' => $request->get('refresh'),
            'template' => $request->get('template')
        ]);

        if ($formHelper->isSubmitted()) {
            return new JsonResponse(['success' => $formHelper->isValid(), 'html' => $render]);
        }

        return new JsonResponse(['html' => $render]);
    }

    /**
     * Export
     *
     * @param Request $request
     * @return Response
     */
    protected function export(Request $request)
    {
        $form = NULL;
        $exportService = $this->exportService ? $this->exportService : ExportCsvService::class;
        $interface = $this->getInterface($this->class);
        $configuration = !empty($interface['configuration']) ? $interface['configuration'] : NULL;
        $filterFields = $configuration && $configuration->searchFilters ? $configuration->searchFilters : [];
        $referEntity = new $this->class();

        $fieldsCount = 0;
        foreach ($filterFields as $filterField) {
            if (method_exists($referEntity, 'get' . ucfirst($filterField))) {
                $fieldsCount++;
            }
        }

        if ($fieldsCount > 0) {

            $form = $this->createForm(FilterType::class, NULL, [
                'website' => $this->getWebsite($request),
                'filterName' => 'searchFilters',
                'interface' => $interface
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entities = $this->subscriber->get(SearchFilterService::class)->execute($request, $form, $interface);
                return $this->subscriber->get($exportService)->execute($entities, $interface);
            }
        }

        if ($request->get('export')) {
            $repository = $this->entityManager->getRepository($interface['classname']);
            $filterBuilder = $repository->createQueryBuilder('e');
            if (!empty($interface['masterField']) && $request->get($interface['masterField'])) {
                $filterBuilder->andWhere('e.' . $interface['masterField'] . ' = :' . $interface['masterField']);
                $filterBuilder->setParameter($interface['masterField'], $request->get($interface['masterField']));
            }
            $entities = $filterBuilder->getQuery()->getResult();
            return $this->subscriber->get($exportService)->execute($entities, $interface);
        }

        return $this->cache('admin/core/form/export.html.twig', [
            'form' => !empty($form) ? $form->createView() : NULL,
            'interface' => $interface
        ]);
    }

    /**
     * Entity position
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    protected function position(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_EDIT');

        if ($request->get('ajax')) {

            $interface = $this->getInterface($this->class);
            $entity = !empty($interface['name']) ? $this->entityManager->getRepository($this->class)->find($request->get($interface['name'])) : NULL;

            if (is_object($entity) && $request->get('position') && method_exists($entity, 'setPosition')) {
                $entity->setPosition($request->get('position'));
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            return new JsonResponse(['success' => true]);

        } else {

            $service = $this->subscriber->get(PositionService::class);
            $service->setVars($this->class, $request);

            $form = $this->createForm(PositionType::class, $service->getEntity(), [
                'data_class' => get_class($service->getEntity()),
                'old_position' => $service->getEntity()->getPosition(),
                'iterations' => $service->getCount()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $service->execute($form, $form->getData());
                return $this->redirect($request->headers->get('referer'));
            }

            return $this->cache('admin/core/form/position.html.twig', [
                'form' => $form->createView(),
                'entity' => $service->getEntity(),
                'interface' => $service->getInterface()
            ]);
        }
    }

    /**
     * Delete entity
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Response
     */
    protected function delete(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_DELETE');

        if ($this->deleteService) {
            $deleteService = $this->subscriber->get($this->deleteService);
            if (method_exists($deleteService, 'execute')) {
                $deleteService->execute();
            }
        }

        $service = $this->subscriber->get(DeleteService::class);
        $service->execute($this->class, $this->entities);

        if ($request->get('ajax')) {
            return new JsonResponse(['success' => true]);
        } elseif ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return new Response();
        }
    }

    /**
     * Get edition arguments (new & edit)
     *
     * @param Request $request
     * @param array $custom
     * @return array
     */
    protected function editionArguments(Request $request, array $custom = [])
    {
        $interface = $this->class
            ? $this->getInterface($this->class)
            : [];

        $entity = $this->entity;
        if (!empty($interface['entity'])) {
            $entity = !empty($this->entity) ? $this->entity : $interface['entity'];
        }

        if ($entity && $entity->getId()) {
            $this->subscriber->get(UrlManager::class)->synchronizeLocales($entity, $interface['website']);
        }

        $arguments = [
            'pageTitle' => $this->pageTitle,
            'entity' => $entity,
            'interface' => $interface,
            'website' => $interface ? $interface['website'] : NULL,
            'request' => $request,
            'entitylocale' => $request->get('entitylocale'),
            'formType' => $this->formType,
            'templateConfig' => $this->templateConfig,
            'formManager' => $this->formManager,
            'disableFlash' => $this->disableFlash,
            'class' => $this->class,
            'formOptions' => $this->formOptions,
            'template' => $this->template,
        ];

        if ($entity && method_exists($entity, 'getLayout')) {
            $arguments['layoutConfiguration'] = $this->entityManager->getRepository(LayoutConfiguration::class)->findOneBy(
                ['entity' => $interface['classname'], 'website' => $interface['website']]
            );
        }

        $arguments = array_merge($arguments, $this->arguments);

        return ['params' => (object)$arguments];
    }

    /**
     * Forward edition view (new & edit)
     *
     * @param mixed $params
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function edition($params)
    {
        /** @var Request $request */
        $request = $params->request;
        $view = debug_backtrace()[4]['function'];
        $template = $params->template ?: 'admin/core/' . $view . '.html.twig';
        $formHelper = $this->subscriber->get(FormHelper::class);
        $formHelper->execute($params->formType, $params->entity, $params->class, $params->formOptions, $params->formManager, $params->disableFlash, $view);
        $entity = $formHelper->getEntity();

        if (method_exists($entity, 'getLayout')) {
            $website = $params->website instanceof Website ? $params->website : $this->getWebsite($this->request);
            $layoutManager = $this->subscriber->get(LayoutManager::class);
            $layoutManager->setLayout($params->interface, $entity, $website);
            $layoutManager->setGridZone($entity->getLayout());
        }

        $arguments = [
            'form' => $formHelper->getForm() ? $formHelper->getForm()->createView() : NULL,
            'entity' => $entity,
            'haveH1' => $formHelper->haveH1(),
            'namespace' => $this->getCurrentNamespace($params->request),
        ];
        $arguments = array_merge((array)$params, $arguments);

        if (!empty($request->get('ajax'))) {
            $redirection = !empty($arguments['redirection']) ? $arguments['redirection'] : NULL;
            $redirectionHost = $redirection && !preg_match('/' . $request->getHost() . '/', $redirection) ? $request->getSchemeAndHttpHost() . $redirection : NULL;
            $success = $formHelper->getForm() && $formHelper->getForm()->isSubmitted() ? $formHelper->getForm()->isValid() : true;
            return new JsonResponse(['success' => $success, 'html' => $this->renderView($template, $arguments), 'redirection' => $redirectionHost]);
        }

        if (!empty($formHelper->getRedirection())) {
            header('Location:' . $formHelper->getRedirection());
            exit();
        }

        ksort($arguments);

        return $this->cache($template, $arguments);
    }

    /**
     * Check entity locale
     *
     * @param Request $request
     */
    protected function checkEntityLocale(Request $request)
    {
        $website = $this->getWebsite($request);
        $localeRequest = $request->get('entitylocale');

        if ($localeRequest && !in_array($localeRequest, $website->getConfiguration()->getAllLocales())) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * Set cache
     *
     * @param string $template
     * @param array $arguments
     * @param Request|null $request
     * @return string|Response
     */
    protected function cache(string $template, array $arguments = [], Request $request = NULL)
    {
        if ($request && $request->get('ajax') || $request && $request->get('jsonResponse')) {

            $responseArguments = ['html' => $this->subscriber->get(CacheService::class)->parse($this->renderView(
                $template,
                $arguments
            ))];

            return $this->renderView('core/render.html.twig', $responseArguments);

        } elseif ($this->kernel->getEnvironment() === 'dev') {

            $date = new \DateTime();
            $date->modify('+3600 seconds');

            $response = $this->render($template, $arguments);
            $response->setETag(md5($response->getContent()));
            $response->setPublic();
            $response->setExpires($date);
            $response->setCharset('UTF-8');

            return $response;
        } else {

            $responseArguments = ['html' => $this->subscriber->get(CacheService::class)->parse($this->renderView(
                $template,
                $arguments
            ))];

            return $this->render('core/render.html.twig', $responseArguments);
        }
    }
}