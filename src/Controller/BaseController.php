<?php

namespace App\Controller;

use App\Command\CacheCommand;
use App\Entity\Core\Website;
use App\Helper\Core\InterfaceHelper;
use App\Service\Core\SubscriberService;
use App\Service\Core\TreeService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BaseController
 *
 * App base controller
 *
 * @property bool DOCTRINE_LOG
 * @property bool DISABLED_PROFILER
 *
 * @property SubscriberService $subscriber
 * @property PaginatorInterface $paginator
 * @property RequestStack $requestStack
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property bool $isDevMode
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
abstract class BaseController extends AbstractController
{
    private const DOCTRINE_LOG = true;
    private const DISABLED_PROFILER = false;

    protected $subscriber;
    protected $paginator;
    protected $requestStack;
    protected $request;
    protected $entityManager;
    protected $kernel;
    protected $translator;
    protected $authorizationChecker;
    protected $isDevMode;

    /**
     * BaseController constructor.
     *
     * @param SubscriberService $subscriber
     * @param RequestStack $requestStack
     * @param PaginatorInterface $paginator
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        SubscriberService $subscriber,
        RequestStack $requestStack,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->subscriber = $subscriber;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->isDevMode = $this->kernel->getEnvironment() === 'dev';

        if (!$_ENV['APP_PROFILER']) {
            $this->disableProfiler();
        }

        if (!self::DOCTRINE_LOG) {
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        }
    }

    /**
     * To clear cache
     *
     * @Route("/app/clear/cache", methods={"GET"}, name="front_clear_cache", options={"expose"=true}, schemes={"%protocol%"})
     *
     * @param CacheCommand $cacheCommand
     * @return JsonResponse
     */
    public function clearAppCache(CacheCommand $cacheCommand): JsonResponse
    {
        if ($this->kernel->getEnvironment() === 'prod') {
            $cacheCommand->clear();
            $websites = $this->entityManager->getRepository(Website::class)->findAll();
            foreach ($websites as $website) {
                $website->setCacheClearDate(new \DateTime('now'));
                $this->entityManager->persist($website);
            }
            $this->entityManager->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        if (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === "dev") {
            $servicesInjection['profiler'] = Profiler::class;
            return array_merge(parent::getSubscribedServices(), $servicesInjection);
        }

        return parent::getSubscribedServices();
    }

    /**
     * Get Request Website
     *
     * @param Request $request
     * @param bool $forceQuery
     * @return Website|array
     */
    protected function getWebsite(Request $request, bool $forceQuery = false)
    {
        $repository = $this->entityManager->getRepository(Website::class);

        if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri())) {
            if (!is_object($request->get('website')) && $request->get('website')) {
                return $repository->find(intval($request->get('website')));
            } elseif (!$request->get('website')) {
                return $repository->findOneByHost($request->getHost());
            }
            return $request->get('website');
        } else {
            $website = $request->getSession()->get('frontWebsiteObject');
            if (!$website || $forceQuery) {
                $website = $repository->findOneByHost($request->getHost());
            } elseif (is_numeric($website)) {
                $website = $repository->findObject(intval($website));
            }
            return $website;
        }
    }

    /**
     * Get Entity Interface
     *
     * @param string $classname
     * @return array
     */
    protected function getInterface(string $classname): array
    {
        $interfaceHelper = $this->subscriber->get(InterfaceHelper::class);
        return $interfaceHelper->generate($classname);
    }

    /**
     * Get current namespace
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getCurrentNamespace(Request $request): ?string
    {
        $matches = explode('::', $request->get('_controller'));
        return !empty($matches) ? $matches[0] : NULL;
    }

    /**
     * Get Tree of Entities
     *
     * @param array|object $entities
     * @return array
     */
    protected function getTree($entities): array
    {
        return $this->subscriber->get(TreeService::class)->execute($entities);
    }

    /**
     * Disabled debug profiler
     */
    protected function disableProfiler()
    {
        if ($this->kernel->getEnvironment() === "dev" && self::DISABLED_PROFILER) {
            $this->get('profiler')->disable();
        }
    }

    /**
     * Generate pagination
     *
     * @param $queryBuilder
     * @param int $queryLimit
     * @return PaginationInterface
     */
    protected function getPagination($queryBuilder, int $queryLimit = 12): PaginationInterface
    {
        return $this->paginator->paginate(
            $queryBuilder,
            $this->request->query->getInt('page', 1),
            $queryLimit,
            ['wrap-queries' => true]
        );
    }
}