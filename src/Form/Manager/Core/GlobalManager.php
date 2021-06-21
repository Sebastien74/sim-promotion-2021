<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Form\Manager\Layout\LayoutManager;
use App\Form\Manager\Media\MediaManager;
use App\Form\Manager\Seo\UrlManager;
use App\Form\Manager\Translation\i18nManager;
use App\Helper\Admin\IndexHelper;
use App\Service\Core\CacheService;
use App\Service\Core\SubscriberService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Sluggable\Util\Urlizer;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GlobalManager
 *
 * Manage all admin forms
 *
 * @property Request $request
 * @property Request $masterRequest
 * @property EntityManagerInterface $entityManager
 * @property CacheService $cacheService
 * @property TranslatorInterface $translator
 * @property IndexHelper $indexHelper
 * @property SubscriberService $serviceSubscriber
 * @property KernelInterface $kernel
 * @property array $interface
 * @property mixed $manager
 * @property mixed $data
 * @property string $masterField
 * @property string $parentMasterField
 * @property Form $form
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GlobalManager
{
    private $request;
    private $masterRequest;
    private $entityManager;
    private $cacheService;
    private $translator;
    private $indexHelper;
    private $serviceSubscriber;
    private $kernel;
    private $interface;
    private $manager;
    private $data;
    private $masterField;
    private $parentMasterField;
    private $form;
    private $website;

    /**
     * GlobalManager constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param CacheService $cacheService
     * @param TranslatorInterface $translator
     * @param IndexHelper $indexHelper
     * @param SubscriberService $serviceSubscriber
     * @param KernelInterface $kernel
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        CacheService $cacheService,
        TranslatorInterface $translator,
        IndexHelper $indexHelper,
        SubscriberService $serviceSubscriber,
        KernelInterface $kernel)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->masterRequest = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->cacheService = $cacheService;
        $this->translator = $translator;
        $this->indexHelper = $indexHelper;
        $this->serviceSubscriber = $serviceSubscriber;
        $this->kernel = $kernel;
    }

    /**
     * On success submission
     *
     * @param array $interface
     * @param mixed|null $formManager
     * @param bool $disableFlash
     */
    public function success(array $interface, $formManager = NULL, bool $disableFlash = false)
    {
        try {

            $this->interface = $interface;
            $this->manager = $formManager
                ? $this->serviceSubscriber->get($formManager)
                : NULL;

            $this->data = $this->form->getData();

            $this->setWebsite($interface);
            $isNew = !$this->data->getId();

            if ($isNew) {
                $this->setMasterField($interface);
                $this->setParentMasterField($interface);
                $this->setPosition($interface);
                $this->callManager('prePersist', $interface);
                $this->setUser();
            } else {
                $this->setIsDefault($interface);
                $this->callManager('preUpdate', $interface);
            }

            $this->setAdminName();
            $this->setSlug();
            $this->setComputeETag();

            $urlManager = $this->serviceSubscriber->get(UrlManager::class);
            $urlManager->post($this->form, $this->website);

            $layoutManager = $this->serviceSubscriber->get(LayoutManager::class);
            $layoutManager->post($interface, $this->form, $this->website);

            $mediaManager = $this->serviceSubscriber->get(MediaManager::class);
            $mediaManager->post($this->form, $this->website, $interface);

            $i18nManager = $this->serviceSubscriber->get(i18nManager::class);
            $i18nManager->post($this->form, $this->website);

            $this->entityManager->persist($this->data);
            $this->entityManager->flush();

            $this->callManager('onFlush', $interface);
            $this->dispatchEvent();

            $sessionManager = $this->serviceSubscriber->get(SessionManager::class);
            $sessionManager->execute($this->request, $this->data);

            $this->cacheService->clearFrontCache($this->website);

            $this->setFlashBag($isNew, 'success', $disableFlash, $interface);

        } catch (\Exception $exception) {

            $session = new Session();
            $session->getFlashBag()->add('error', $this->translator->trans("Une erreur est survenue : ", [], 'admin') . $exception->getMessage());

            $logger = new Logger('form.global.manager');
            $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/admin.log', 10, Logger::CRITICAL));
            $logger->critical($exception->getMessage() . ' at ' . get_class($this) . ' line ' . $exception->getLine());
        }
    }

    /**
     * On invalid submission
     *
     * @param Form|FormInterface $form
     */
    public function invalid($form)
    {
        $message = '';

        foreach ($form->getErrors() as $error) {
            $message .= $error->getMessage();
            if (method_exists($error, 'getCause') && $error->getCause()
                && is_object($error->getCause()) && method_exists($error->getCause(), 'getPropertyPath')) {
                $message .= ' [' . $error->getCause()->getPropertyPath() . ']<br>';
            }
            $message .= '<br>';
        }

        $message = $message ? $message : $this->translator->trans('Une erreur est survenue !', [], 'admin');

        if ($message) {
            $session = new Session();
            $session->getFlashBag()->add('error', rtrim($message, '<br>'));
        }
    }

    /**
     * Set Website
     *
     * @param Form $form
     */
    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    /**
     * Set Website
     *
     * @param array $interface
     */
    public function setWebsite(array $interface): void
    {
        $this->website = $interface['website'] instanceof Website ?
            $interface['website'] : ($this->masterRequest->get('website')
                ? $this->entityManager->getRepository(Website::class)->find($this->masterRequest->get('website')) : NULL);

        if (method_exists($this->data, 'getWebsite') && !$this->data->getWebsite()) {
            $this->data->setWebsite($this->website);
        }
    }

    /**
     * Set masterField
     *
     * @param array $interface
     */
    public function setMasterField(array $interface): void
    {
        $this->masterField = $interface['masterField'];

        if ($this->masterField && $this->masterField === 'website') {
            $this->data->setWebsite($this->website);
        } elseif ($this->masterField && $this->masterField === 'configuration') {
            $this->data->setConfiguration($this->website->getConfiguration());
        }
    }

    /**
     * Set parentMasterField
     *
     * @param array $interface
     */
    public function setParentMasterField(array $interface): void
    {
        $this->parentMasterField = $interface['parentMasterField'];
        $parentRequest = $this->parentMasterField ? $this->masterRequest->get($this->parentMasterField) : NULL;
        $mapping = $this->entityManager->getClassMetadata($interface['classname'])->getAssociationMappings();

        $setter = 'set' . ucfirst($interface['parentMasterField']);
        if (!empty($parentRequest) && method_exists($this->data, $setter) && !empty($mapping[$this->parentMasterField])) {
            $parent = $this->entityManager->getRepository($mapping[$this->parentMasterField]['targetEntity'])->find(intval($parentRequest));
            $this->data->$setter($parent);
        }
    }

    /**
     * Set position
     *
     * @param array $interface
     */
    public function setPosition(array $interface): void
    {
        $this->indexHelper->setDisplaySearchForm(false);
        $this->indexHelper->execute($interface['classname'], $interface);

        $pagination = $this->indexHelper->getPagination();

        if (is_array($pagination)) {
            $position = count($pagination) + 1;
        } else {
            $position = method_exists($pagination, 'getTotalItemCount') ? $pagination->getTotalItemCount() + 1 : 1;
        }

        if (method_exists($this->data, 'setPosition')) {
            $this->data->setPosition($position);
        }
    }

    /**
     * Set is default unique
     *
     * @param array $interface
     */
    public function setIsDefault(array $interface): void
    {
        if (method_exists($this->data, 'setIsDefault') && !empty($interface['classname'])) {

            $existing = $this->entityManager->getRepository($interface['classname'])->findOneBy([
                'website' => $this->website,
                'isDefault' => true,
            ]);

            if ($existing && $existing->getId() !== $this->data->getId()) {
                $existing->setIsDefault(false);
                $this->entityManager->persist($existing);
            }
        }
    }

    /**
     * Set adminName
     */
    public function setAdminName(): void
    {
        if (method_exists($this->data, 'getAdminName') && empty($this->data->getAdminName())) {

            if (method_exists($this->data, 'getI18n') && !empty($this->data->getI18n())) {
                /** @var $i18n i18n */
                $i18n = $this->data->getI18n();
                $this->data->setAdminName($i18n->getTitle());
            }

            if (method_exists($this->data, 'getI18ns')) {
                foreach ($this->data->getI18ns() as $i18n) {
                    /** @var $i18n i18n */
                    if ($i18n->getLocale() === $this->website->getConfiguration()->getLocale()) {
                        $this->data->setAdminName($i18n->getTitle());
                    }
                }
            }
        }
    }

    /**
     * Set slug
     */
    private function setSlug()
    {
        if (method_exists($this->data, 'getSlug')
            && method_exists($this->data, 'getAdminName')
            && empty($this->data->getSlug()) && !empty($this->interface['classname'])) {

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $this->entityManager->getRepository($this->interface['classname'])->createQueryBuilder('e');
            if (!empty($this->masterField) && !empty($this->masterRequest->get($this->masterField))) {
                $queryBuilder->andWhere('e.' . $this->masterField . ' = :' . $this->masterField);
                $queryBuilder->setParameter($this->masterField, $this->masterRequest->get($this->masterField));
            }

            $slug = Urlizer::urlize($this->data->getAdminName());
            $queryBuilder->andWhere('e.slug = :slug');
            $queryBuilder->setParameter('slug', $slug);
            $existing = $queryBuilder->getQuery()->getResult();
            $slug = $existing && $slug ? $slug . '-' . uniqid() : $slug;

            $this->data->setSlug($slug);
            $this->entityManager->persist($this->data);
        }
    }

    /**
     * Set Compute ETag
     */
    private function setComputeETag()
    {
        if (method_exists($this->data, 'setComputeETag') && empty($this->data->getComputeETag())) {
            $this->data->setComputeETag(uniqid() . md5($this->data->getId()));
            $this->entityManager->persist($this->data);
        }
    }

    /**
     * Set Session Flash bag
     *
     * @param bool $isNew
     * @param string $type
     * @param bool $disableFlash
     * @param array $interface
     */
    private function setFlashBag(bool $isNew, string $type, $disableFlash, array $interface = [])
    {
        $isDisabled = $disableFlash || !empty($interface['disabled_flash_bag']) && $interface['disabled_flash_bag'];

        if (!$isDisabled) {

            $message = $isNew
                ? $this->translator->trans('Créé avec succès !!', [], 'admin')
                : $message = $this->translator->trans('Modifié avec succès !!', [], 'admin');

            $session = new Session();
            $session->getFlashBag()->add($type, $message);
        }
    }

    /**
     * Call form manager
     *
     * @param string $method
     * @param array $interface
     */
    private function callManager(string $method, array $interface)
    {
        if (is_object($this->manager) && method_exists($this->manager, $method)) {
            $this->manager->$method($this->data, $this->website, $interface, $this->form);
        }
    }

    /**
     * Dispatch event
     *
     * @param array $interface
     * @param null|object $data
     */
    private function dispatchEvent(array $interface = [], $data = NULL)
    {
        $interface = $interface ? $interface : $this->interface;
        $data = $data ? $data : $this->data;
        $classname = !empty($interface['classname']) ? $interface['classname'] : NULL;
        $postType = is_object($data) && method_exists($data, 'getId') && $data->getId()
            ? 'Updated' : 'Created';
        $matches = explode('\\', $classname);
        $eventName = '\App\Event\\' . end($matches) . $postType . 'Event';
        $subscriberName = '\App\EventSubscriber\\' . end($matches) . 'Subscriber';

        if ($classname && class_exists($eventName) && class_exists($subscriberName)) {
            $dispatcher = new EventDispatcher();
            $subscriber = new $subscriberName();
            $dispatcher->addSubscriber($subscriber);
            $event = new $eventName($data);
            $dispatcher->dispatch($event, $eventName::NAME);
        }
    }

    /**
     * To set User create
     */
    private function setUser()
    {
        $container = $this->kernel->getContainer();
        $token = $container->get('security.token_storage')->getToken();

        if (method_exists($token, 'getUser') && $token->getUser() && method_exists($this->data, 'setCreatedBy')) {
            $this->data->setCreatedBy($token->getUser());
        }
    }
}