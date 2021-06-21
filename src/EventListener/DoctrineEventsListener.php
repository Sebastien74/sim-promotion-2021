<?php

namespace App\EventListener;

use App\Entity\Core\Website;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationUnit;
use App\Helper\Core\InterfaceHelper;
use App\Service\Core\CacheService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * DoctrineEventsListener
 *
 * Listen Doctrine events
 *
 * @property EntityManagerInterface $entityManager
 * @property bool $isDebug
 * @property KernelInterface $kernel
 * @property InterfaceHelper $interfaceHelper
 * @property CacheService $cacheService
 * @property mixed $entity
 * @property string $login
 * @property string $classname
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DoctrineEventsListener implements EventSubscriber
{
    private const DISABLED_ENTITIES = [
        Translation::class,
        TranslationUnit::class,
        TranslationDomain::class
    ];

    private $entityManager;
    private $isDebug;
    private $kernel;
    private $interfaceHelper;
    private $cacheService;
    private $entity;
    private $login;
    private $classname;

    /**
     * DoctrineOnFlushListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param bool $isDebug
     * @param KernelInterface $kernel
     * @param InterfaceHelper $interfaceHelper
     * @param CacheService $cacheService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        bool $isDebug,
        KernelInterface $kernel,
        InterfaceHelper $interfaceHelper,
        CacheService $cacheService)
    {
        $this->entityManager = $entityManager;
        $this->isDebug = $isDebug;
        $this->kernel = $kernel;
        $this->interfaceHelper = $interfaceHelper;
        $this->cacheService = $cacheService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::postPersist
        ];
    }

    /**
     * postUpdate
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->process($args, 'update');
    }

    /**
     * postPersist
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->process($args, 'persist');
    }

    /**
     * Process
     *
     * @param LifecycleEventArgs $args
     * @param string $type
     */
    private function process(LifecycleEventArgs $args, string $type)
    {
        $this->entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $metadata = $entityManager->getClassMetadata(get_class($this->entity));

        $this->classname = $metadata->getName();
        $this->login = method_exists($this->entity, 'getCreatedBy') && $this->entity->getCreatedBy()
            ? $this->entity->getCreatedBy()->getLogin() : NULL;

        $this->logger($type);

        if (!$this->disabledCache()) {
            $this->clearHtmlCache();
        }
    }

    /**
     * Clear HTML cache
     */
    private function clearHtmlCache()
    {
        $session = new Session();
        /** @var Website $website */
        $website = $session->get('adminWebsite');

        if ($website) {
            $this->cacheService->clearFrontCache($website);
        }
    }

    /**
     * Check if is Listener request
     *
     * @return bool
     */
    private function disabledCache(): bool
    {
        $appListenerDirname = $this->kernel->getProjectDir() . '\\src\\EventListener';
        $appSubscriberDirname = $this->kernel->getProjectDir() . '\\src\\EventSubscriber';

        if (is_object($this->entity) && in_array(get_class($this->entity), self::DISABLED_ENTITIES) && empty($_POST)) {
            return true;
        }

        foreach (debug_backtrace() as $backtrace) {
            $file = is_array($backtrace) && !empty($backtrace['file']) ? $backtrace['file'] : NULL;
            $doctrineEvent = $file && preg_match('/DoctrineEventsListener/', $file);
            $appListener = $file && preg_match('/' . $this->formatDirname($appListenerDirname) . '/', $this->formatDirname($file));
            $appSubscriber = $file && preg_match('/' . $this->formatDirname($appSubscriberDirname) . '/', $this->formatDirname($file));
            if (!$doctrineEvent && $appListener || !$doctrineEvent && $appSubscriber) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format dirname
     *
     * @param string|null $dirname
     * @return string|null
     */
    private function formatDirname(string $dirname = NULL): ?string
    {
        if ($dirname) {
            return str_replace(['\\', '/'], '-', $dirname);
        }

        return NULL;
    }

    /**
     * Logger
     *
     * @param string $type
     */
    private function logger(string $type)
    {
        $entityID = is_object($this->entity) && method_exists($this->entity, 'getId') ? $this->entity->getId() : NULL;

        $logger = new Logger('form.' . $type);
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/admin-form-update.log', 10, Logger::INFO));
        $logger->info('[Classname] ' . $this->classname . ' - [ID] ' . $entityID . ' - [User] ' . $this->login);
    }
}