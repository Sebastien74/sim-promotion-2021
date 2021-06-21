<?php

namespace App\EventSubscriber;

use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * LocaleSubscriber
 *
 * User locale subscriber
 *
 * @property string $defaultLocale
 * @property EntityManagerInterface $entityManager
 * @property ContainerInterface $container
 * @property User $user
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;
    private $entityManager;
    private $container;
    private $user;

    /**
     * LocaleSubscriber constructor.
     *
     * @param string $defaultLocale
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * onKernelRequest
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route');

        if (!$event->isMasterRequest() || $routeName === 'app_new_website_project') {
            return;
        }

        $uri = $request->getUri();
        $hasSwitch = !empty($request->get('_switch_user'));

        if (!$request->hasPreviousSession() && !$event->isMasterRequest() || $hasSwitch) {
            return;
        }

        if (!preg_match('/_fragment/', $uri) && !preg_match('/_wdt/', $uri)) {

            $website = $this->getWebsite($event, $uri);

            /** Front request */
            if (!preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri)) {
                $sessionDomain = $request->getSession()->get('front_current_domain');
                /** @var Domain $domain */
                $domain = $sessionDomain instanceof Domain ? $sessionDomain : $this->entityManager->getRepository(Domain::class)->findOneBy(['name' => $request->getHost()]);
                if(is_array($website) && !empty($website['configuration'])) {
					$configuration = $website['configuration'];
				} else {
					$configuration = $website instanceof Website ? $website->getConfiguration() : NULL;
				}
                $locale = $domain ? $domain->getLocale() : ($configuration instanceof Configuration ? $configuration->getLocale() : (is_array($configuration) ? $configuration['locale'] : $request->getLocale()));
                $request->getSession()->set('_locale', $locale);
                $request->setLocale($locale);
            } /** Try to see if the locale has been set as a _locale routing parameter */
            elseif ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->set('_locale', $locale);
            } /** If no explicit locale has been set on this request, use one from the session */
            else {
                $tokenStorage = $this->container->get('security.token_storage');
                if (!empty($tokenStorage->getToken())) {
                    $this->user = $tokenStorage->getToken()->getUser();
                    if ($this->user && method_exists($this->user, 'getLocale') && $this->user->getLocale()) {
                        $request->getSession()->set('_locale', $this->user->getLocale());
                    }
                }
            }
        }
    }

    /**
     * Get Website
     *
     * @param RequestEvent $event
     * @param string $uri
     * @return Website
     */
    private function getWebsite(RequestEvent $event, string $uri)
    {
        if (!preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri)) {
            /** @var Website $website */
            $website = $event->getRequest()->getSession()->get('frontWebsite');
        } else {
            /** @var Website $website */
            $website = $event->getRequest()->getSession()->get('adminWebsite');
        }

        if (!$website && !preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri)) {
            /** @var Website $website */
            $website = $this->entityManager->getRepository(Website::class)->findOneByHost($event->getRequest()->getHost());
            $event->getRequest()->getSession()->set('frontWebsite', $website);
        }

        return $website;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            /** must be registered before (i.e. with a higher priority than) the default Locale listener */
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
