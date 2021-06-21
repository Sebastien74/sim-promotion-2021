<?php

namespace App\Helper\Admin;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Layout\Zone;
use App\Entity\Media\Media;
use App\Form\Manager\Core\GlobalManager;
use App\Form\Manager\Media\MediaManager;
use App\Form\Manager\Seo\UrlManager;
use App\Form\Manager\Translation\i18nManager;
use App\Form\Type\Core\DefaultType;
use App\Helper\Core\InterfaceHelper;
use App\Repository\Core\WebsiteRepository;
use App\Twig\Core\AppRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormHelper
 *
 * @property Request $request
 * @property Request $currentRequest
 * @property InterfaceHelper $interfaceHelper
 * @property EntityManagerInterface $entityManager
 * @property FormFactoryInterface $formFactory
 * @property GlobalManager $globalManager
 * @property MediaManager $mediaManager
 * @property i18nManager $i18nManager
 * @property UrlManager $urlManager
 * @property TranslatorInterface $translator
 * @property WebsiteRepository $websiteRepository
 * @property AppRuntime $appExtension
 * @property KernelInterface $kernel
 * @property RouterInterface $router
 * @property bool $disableFlash
 * @property array $interface
 * @property Website $website
 * @property mixed $entity
 * @property Form $form
 * @property string $redirection
 * @property bool $haveH1
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormHelper
{
    private $request;
    private $currentRequest;
    private $interfaceHelper;
    private $entityManager;
    private $formFactory;
    private $globalManager;
    private $mediaManager;
    private $i18nManager;
    private $urlManager;
    private $translator;
    private $websiteRepository;
    private $appExtension;
    private $kernel;
    private $router;
    private $disableFlash = false;
    private $interface;
    private $website;
    private $entity;
    private $form;
    private $redirection;
    private $haveH1 = true;

    /**
     * FormHelper constructor.
     *
     * @param RequestStack $requestStack
     * @param InterfaceHelper $interfaceHelper
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param GlobalManager $globalManager
     * @param MediaManager $mediaManager
     * @param i18nManager $i18nManager
     * @param UrlManager $urlManager
     * @param TranslatorInterface $translator
     * @param WebsiteRepository $websiteRepository
     * @param AppRuntime $appExtension
     * @param KernelInterface $kernel
     * @param RouterInterface $router
     */
    public function __construct(
        RequestStack $requestStack,
        InterfaceHelper $interfaceHelper,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        GlobalManager $globalManager,
        MediaManager $mediaManager,
        i18nManager $i18nManager,
        UrlManager $urlManager,
        TranslatorInterface $translator,
        WebsiteRepository $websiteRepository,
        AppRuntime $appExtension,
        KernelInterface $kernel,
        RouterInterface $router)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->currentRequest = $requestStack->getCurrentRequest();
        $this->interfaceHelper = $interfaceHelper;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->globalManager = $globalManager;
        $this->mediaManager = $mediaManager;
        $this->i18nManager = $i18nManager;
        $this->urlManager = $urlManager;
        $this->translator = $translator;
        $this->websiteRepository = $websiteRepository;
        $this->appExtension = $appExtension;
        $this->kernel = $kernel;
        $this->router = $router;
    }

    /**
     * Execute FormHelper
     *
     * @param string|null $formType
     * @param mixed|null $entity
     * @param string|null $classname
     * @param array $options
     * @param mixed|null $formManager
     * @param bool $disableFlash
     * @param string|null $view
     * @return void
     * @throws NonUniqueResultException
     */
    public function execute(
        string $formType = NULL,
        $entity = NULL,
        string $classname = NULL,
        array $options = [],
        $formManager = NULL,
        bool $disableFlash = false,
        string $view = NULL)
    {
        $this->disableFlash = $disableFlash;

        if ($classname) {

            $this->setInterface($classname);
            $this->setWebsite();
            $this->setEntity($entity, $classname, $view);
            $this->checkH1();

            if ($view != 'new') {
                $this->setI18ns();
                $this->setMediaRelations();
                $this->setMediaRelation();
                if ($this->entity instanceof Media) {
                    $this->setMediaScreen($this->entity);
                }
            }

            $this->setForm($formType, $options);
            $this->submit($formManager);
        }
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
     * Set Website
     */
    public function setWebsite(): void
    {
        $websiteRequest = $this->request->get('website')
            ? $this->request->get('website')
            : $this->request->get('site');

        $this->website = $this->websiteRepository->find($websiteRequest);
    }

    /**
     * Set Entity
     *
     * @param mixed|null $entity
     * @param string|null $classname
     * @param string|null $view
     * @throws NonUniqueResultException
     */
    public function setEntity($entity = NULL, string $classname = NULL, string $view = NULL): void
    {
        $entityRequest = NULL;
        if($this->interface['name']) {
            $entityRequest = $this->request->get($this->interface['name'])
                ? $this->request->get($this->interface['name'])
                : $this->currentRequest->get($this->interface['name']);
        }

        if ($entity) {
            $this->entity = $entity;
        } elseif ($view === 'new') {
            $this->entity = new $classname();
        } elseif ($view === 'layout') {
            $this->entity = $this->getLayout($classname, intval($entityRequest));
        } else {
            $this->entity = $entityRequest && !is_array($entityRequest)
                ? $this->entityManager->getRepository($classname)->find($entityRequest)
                : $this->interface['entity'];
        }

        $this->setMasterField();

        if ($this->entity && property_exists($this->entity, 'locale') && !$this->entity->getLocale()) {
            $locale = $this->request->get('entitylocale') ? $this->request->get('entitylocale') : $this->website->getConfiguration()->getLocale();
            $this->entity->setLocale($locale);
        }

        if (!$this->entity) {
            throw new NotFoundHttpException($this->translator->trans("Aucune entité trouvée.", [], 'admin'));
        }
    }

    /**
     * Check if H1 existing
     */
    private function checkH1()
    {
        if ($this->entity instanceof Page && $this->entity->getId()
            || method_exists($this->entity, 'getCustomLayout') && $this->entity->getCustomLayout() && $this->entity->getId()) {

            $result = [];
            $locales = $this->website->getConfiguration()->getAllLocales();

            foreach ($locales as $locale) {
                $existing = $this->entityManager->getRepository(Block::class)->findTitleByForceAndLocalePage($this->entity, $locale, 1, true);
                $result[$locale] = $existing ? count($existing) : 0;
                if ($result[$locale] === 0 || $result[$locale] > 1) {
                    $result['error'] = true;
                }
            }

            $this->haveH1 = $result;
        }
    }

    /**
     * Have H1
     *
     * @return bool
     */
    public function haveH1(): bool
    {
        return is_bool($this->haveH1) ? $this->haveH1 : !empty($this->haveH1);
    }

    /**
     * Get Layout
     *
     * @param string $classname
     * @param int $entityId
     * @return Layout|null
     * @throws NonUniqueResultException
     */
    private function getLayout(string $classname, int $entityId): ?Layout
    {
        $referEntity = new $classname();

        $queryBuilder = $this->entityManager->createQueryBuilder()->select('e')
            ->from($classname, 'e')
            ->leftJoin('e.layout', 'l')
            ->leftJoin('l.zones', 'z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('c.blocks', 'b')
            ->leftJoin('b.action', 'ba')
            ->leftJoin('b.actionI18ns', 'bai')
            ->leftJoin('b.i18ns', 'bi')
            ->leftJoin('b.blockType', 'bbt')
            ->andWhere('e.id = :id')
            ->setParameter('id', $entityId)
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addSelect('b')
            ->addSelect('ba')
            ->addSelect('bai')
            ->addSelect('bi')
            ->addSelect('bbt');

        if (method_exists($referEntity, 'getUrls')) {
            $queryBuilder->leftJoin('e.urls', 'u')
                ->leftJoin('u.seo', 's')
                ->addSelect('u')
                ->addSelect('s');
        }

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Set Entity MasterField
     */
    private function setMasterField()
    {
        if ($this->entity && !empty($this->interface['masterField']) && property_exists($this->entity, $this->interface['masterField'])) {

            $masterEntity = NULL;

            if ($this->interface['masterField'] === "configuration") {
                $masterEntity = $this->website->getConfiguration();
            } else {
                $metadata = $this->entityManager->getClassMetadata($this->interface['classname']);
                $masterClassname = $metadata->associationMappings[$this->interface['masterField']]['targetEntity'];
                if (!empty($this->request->get($this->interface['masterField']))) {
                    $masterEntity = $this->entityManager->getRepository($masterClassname)->find($this->interface['masterFieldId']);
                }
            }

            if (!empty($masterEntity)) {
                $setter = 'set' . ucfirst($this->interface['masterField']);
                $this->entity->$setter($masterEntity);
            }
        }
    }

    /**
     * Synchronize locale MediaRelation
     */
    private function setMediaRelation()
    {
        if (method_exists($this->entity, 'getMediaRelation')) {
            $this->mediaManager->setEntityLocale($this->interface, $this->entity);
        }
    }

    /**
     * Synchronize locales MediaRelation[]
     */
    private function setMediaRelations()
    {
        if (method_exists($this->entity, 'getMediaRelations') && !$this->entity instanceof Media) {
            $this->mediaManager->setMediaRelations($this->entity, $this->website, $this->interface);
        }
    }

    /**
     * Synchronize locale i18n[]
     */
    private function setI18ns()
    {
        $this->i18nManager->synchronizeLocales($this->entity, $this->website);
    }

    /**
     * Synchronize locale Media screens
     *
     * @param Media $media
     */
    private function setMediaScreen(Media $media)
    {
        $this->mediaManager->synchronizeScreens($media);
    }

    /**
     * Get Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get Form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set Form
     *
     * @param string|null $formType
     * @param array $options
     */
    public function setForm(string $formType = NULL, array $options = []): void
    {
        $formType = !empty($formType) ? $formType : DefaultType::class;
        if ($formType === DefaultType::class) {
            $options['data_class'] = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($this->entity))->getName();
        }
        if (empty($options['website'])) {
            $options['website'] = $this->website;
        }

        if (isset($options['form_name'])) {
            $formName = $options['form_name'];
            unset($options['form_name']);
            $this->form = $this->formFactory->createNamedBuilder($formName, $formType, $this->entity, $options)->getForm();
        } else {
            try {
                $this->form = $this->formFactory->create($formType, $this->entity, $options);
            } catch (Exception $exception) {
                throw new HttpException($exception->getCode(), $exception->getMessage());
            }
        }
    }

    /**
     * Form submission process
     *
     * @param mixed|null $formManager
     */
    public function submit($formManager = NULL)
    {
        try {
            if ($this->form) {
                $this->globalManager->setForm($this->form);
                $this->form->handleRequest($this->request);
                if ($this->form->isSubmitted() && $this->form->isValid()) {
                    $this->globalManager->success($this->interface, $formManager, $this->disableFlash);
                    $this->setRedirection();
                } elseif ($this->form->isSubmitted() && !$this->form->isValid()) {
                    $this->globalManager->invalid($this->form);
                }
            }
        } catch (\Exception $exception) {
            $session = new Session();
            $message = $exception->getMessage();
            if (!$message && !is_string($message) && method_exists($exception, 'getPrevious')) {
                $previous = $exception->getPrevious();
                if (method_exists($previous, 'getTrace') && is_iterable($previous->getTrace())) {
                    foreach ($previous->getTrace() as $trace) {
                        if (is_array($trace) && !empty($trace['file']) && !empty($trace['args'][0]) && !empty($trace['args'][2]) && is_array($trace['args'])) {
                            $message = $trace['args'][0];
                            if (!empty($trace['args'][2]) && is_array($trace['args'][2])) {
                                foreach ($trace['args'][2] as $pattern => $value) {
                                    $message = str_replace($pattern, $value, $message);
                                }
                                break;
                            }
                        }
                    }
                }
            }
            $session->getFlashBag()->add('error', $message);
        }
    }

    /**
     * Get Redirection
     *
     * @return string|null
     */
    public function getRedirection(): ?string
    {
        return $this->redirection;
    }

    /**
     * Set Redirection
     *
     * @param string|null $redirection
     */
    public function setRedirection(string $redirection = NULL): void
    {
        try {

            $session = new Session();
            $clickedButton = $this->form->getClickedButton();
            $clickedButtonName = method_exists($clickedButton, 'getName') ? $clickedButton->getName() : NULL;
            $saveEditRoute = 'admin_' . $this->interface['name'] . '_edit';
            $interfaceName = ($this->interface['entity'] instanceof Block || $this->interface['entity'] instanceof Zone)
                ? $this->request->get('interfaceName') : $this->interface['name'];
            $saveLayoutRoute = 'admin_' . $interfaceName . '_layout';
            $parameters = $this->getRouteParameters($saveEditRoute);

            if (!empty($redirection)) {
                $this->redirection = $redirection;
            } elseif ($this->appExtension->routeExist($saveLayoutRoute) && $clickedButtonName === 'saveBack'
                && ($this->interface['entity'] instanceof Block || $this->interface['entity'] instanceof Zone)) {
                $this->redirection = $this->router->generate($saveLayoutRoute, $parameters);
            } elseif ($clickedButtonName === 'saveEdit' && $this->appExtension->routeExist($saveEditRoute)) {
                $this->redirection = $this->router->generate($saveEditRoute, $parameters);
            } elseif ($clickedButtonName === 'saveEdit' && $this->appExtension->routeExist($saveLayoutRoute)) {
                $this->redirection = $this->router->generate($saveLayoutRoute, $parameters);
            } elseif ($clickedButtonName === 'saveBack') {
                $lastRoute = $session->get('last_route_back');
                $this->redirection = $this->router->generate($lastRoute->name, $lastRoute->params);
            } else {
                $this->redirection = $this->request->headers->get('referer');
            }
        } catch (\Exception $exception) {
            $this->logger($exception);
            $this->redirection = $this->request->headers->get('referer');
        }
    }

    /**
     * Get route para
     *
     * @param string $saveEditRoute
     * @return array
     */
    private function getRouteParameters(string $saveEditRoute): array
    {
        $parameters = [];
        $parameters['website'] = $this->website->getId();
        $routeInfos = $this->router->getRouteCollection()->get($saveEditRoute);
        $interfaceNameParameter = $this->request->get('interfaceName');
        $interfaceEntityParameter = $this->request->get('interfaceEntity');

        if ($routeInfos) {
            $path = $this->router->getRouteCollection()->get($saveEditRoute)->getPath();
            if (preg_match('/{entitylocale}/', $path)) {
                $parameters['entitylocale'] = $this->request->get('entitylocale')
                    ? $this->request->get('entitylocale')
                    : $this->website->getConfiguration()->getLocale();
            }
        }

        if ($this->interface['entity'] instanceof Block || $this->interface['entity'] instanceof Zone) {
            $parameters[$interfaceNameParameter] = intval($interfaceEntityParameter);
        } else {
            $parameters[$this->interface['name']] = $this->form->getData()->getId();
            if (!empty($this->interface['masterFieldId']) && $this->interface['masterField'] !== 'configuration') {
                $parameters[$this->interface['masterField']] = $this->interface['masterFieldId'];
            }
        }

        return $parameters;
    }

    /**
     * Logger
     *
     * @param Exception $exception
     */
    private function logger(Exception $exception)
    {
        $logger = new Logger('form.helper');
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/admin.log', 10, Logger::CRITICAL));
        $logger->critical($exception->getMessage() . ' at ' . get_class($this) . ' line ' . $exception->getLine());

        if (!preg_match('/saveEdit/', $exception->getMessage()) && !preg_match('/save/', $exception->getMessage())) {
            $session = new Session();
            $session->getFlashBag()->add('error', $exception->getMessage());
        }
    }
}