<?php

namespace App\Service\Content;

use App\Entity\Module\Newscast\Newscast;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Helper\Core\InterfaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * LocaleService
 *
 * Manage switch route by locale
 *
 * @property EntityManagerInterface $entityManager
 * @property RouterInterface $router
 * @property InterfaceHelper $interfaceHelper
 * @property Request request
 * @property array $localesWebsites
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LocaleService
{
    private $entityManager;
    private $router;
    private $interfaceHelper;
    private $request;
    private $localesWebsites = [];

    /**
     * LocaleService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RouterInterface $router
     * @param InterfaceHelper $interfaceHelper
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        InterfaceHelper $interfaceHelper,
        RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->interfaceHelper = $interfaceHelper;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Execute service
     *
     * @param Website $website
     * @param null $entity
     * @param array|null $url
     * @param string|null $pageUrl
     * @return array
     */
    public function execute(Website $website, $entity = NULL, array $url = NULL, string $pageUrl = NULL): array
    {
        $this->localesWebsites = $this->getLocalesWebsites($website);
        $routeName = $this->request->get('_route');
        $requestUrl = !empty($url['code']) ? $url['code'] : $this->request->get('url');
        $locales = $website->getConfiguration()->getOnlineLocales();
        $classname = get_class($entity);
        $interface = $this->interfaceHelper->generate($classname);
        $interfaceName = !empty($interface['name']) ? $interface['name'] : NULL;
        $asCard = !empty($interface['hasCard']) && $interface['hasCard'];

        $entity = $entity ?: NULL;
        $urlCodes = [];
        $urlIndexCodes = [];

        if ($routeName === 'front_index' && !$requestUrl) {
            return $this->localesWebsites;
        }

        if ($routeName === 'front_index') {
            $entity = $this->getEntity(Page::class, $requestUrl, $website);
            $urlCodes = $this->getUrlCodes($locales, $entity);
        } elseif ($routeName === 'front_' . $interfaceName . '_view_only' || $asCard && !$pageUrl) {
            $urlCodes = $this->getUrlCodes($locales, $entity);
        } elseif ($routeName === 'front_' . $interfaceName . '_view' || $asCard) {
            $pageUrl = $pageUrl ?: $this->request->get('pageUrl');
            $pageIndex = $this->getEntity(Page::class, $pageUrl, $website);
            $urlIndexCodes = $this->getUrlCodes($locales, $pageIndex);
            $entity = $this->getEntity($classname, $requestUrl, $website);
            $urlCodes = $this->getUrlCodes($locales, $entity);
        }

        return $entity ? $this->getUrls($locales, $entity, $urlIndexCodes, $urlCodes) : $this->localesWebsites;
    }

    /**
     * Set locales
     *
     * @param Website|null $website
     * @return array
     */
    public function getLocalesWebsites(Website $website = NULL): array
    {
        $localesWebsites = [];
        $configuration = $website->getConfiguration();
        $configurationLocale = $configuration->getLocale();
        $onlineLocales = $configuration->getOnlineLocales();
        $locales = $onlineLocales ?: [$configurationLocale];
        $protocol = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])] . '://';

        foreach ($locales as $locale) {
            $localeWebsite = $this->entityManager->getRepository(Domain::class)->findDefaultByConfigurationAndLocaleArray($configuration, $locale);
            if ($localeWebsite) {
                $localesWebsites[$locale] = $protocol . $localeWebsite['name'];
            }
        }

        if (empty($localesWebsites[$configurationLocale])) {
            $localesWebsites[$configurationLocale] = $this->request->getSchemeAndHttpHost();
        }

        return $localesWebsites;
    }

    /**
     * Get entity by url code and current request locale
     *
     * @param string $classname
     * @param string $code
     * @param Website $website
     * @return array
     */
    private function getEntity(string $classname, string $code, Website $website): array
    {
        $entity = $this->entityManager->createQueryBuilder()->select('e')
            ->from($classname, 'e')
            ->leftJoin('e.urls', 'u')
            ->andWhere('u.code = :code')
            ->andWhere('u.isOnline = :isOnline')
            ->andWhere('u.locale = :locale')
            ->andWhere('e.website = :website')
            ->setParameter('code', $code)
            ->setParameter('isOnline', true)
            ->setParameter('locale', $this->request->getLocale())
            ->setParameter('website', $website)
            ->addSelect('u')
            ->getQuery()
            ->getArrayResult();

        return !empty($entity[0]) ? $entity[0] : [];
    }

    /**
     * Get codes URL
     *
     * @param array $locales
     * @param mixed $entity
     * @return array
     */
    private function getUrlCodes(array $locales, $entity): array
    {
        $urlCodes = [];

        foreach ($locales as $locale) {
            if (method_exists($entity, 'getUrls')) {
                foreach ($entity->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url->getLocale() === $locale && $url->getIsOnline()) {
                        $urlCodes[$locale] = $entity instanceof Page && $entity->getIsIndex() ? NULL : $url->getCode();
                        break;
                    }
                }
            }
        }

        return $urlCodes;
    }

    /**
     * Get locales URLS
     *
     * @param $locales
     * @param $entity
     * @param array $urlIndexCodes
     * @param array $urlCodes
     * @return array
     */
    private function getUrls($locales, $entity, array $urlIndexCodes, array $urlCodes): array
    {
        $urls = [];

        foreach ($locales as $locale) {

            $domain = !empty($this->localesWebsites[$locale]) ? $this->localesWebsites[$locale] : NULL;

            if ($domain && $entity instanceof Page && !empty($urlCodes[$locale])) {
                $urls[$locale] = $domain . $this->router->generate('front_index', ['url' => $urlCodes[$locale]]);
            } elseif ($domain && $entity instanceof Newscast && !empty($urlIndexCodes[$locale]) && !empty($urlCodes[$locale])) {
                $urls[$locale] = $domain . $this->router->generate('front_newscast_view', ['_locale' => $locale, 'pageUrl' => $urlIndexCodes[$locale], 'url' => $urlCodes[$locale]]);
            } elseif ($domain && $entity instanceof Newscast && empty($urlIndexCodes[$locale]) && !empty($urlCodes[$locale])) {
                $urls[$locale] = $domain . $this->router->generate('front_newscast_view_only', ['_locale' => $locale, 'url' => $urlCodes[$locale]]);
            } elseif ($domain) {
                $urls[$locale] = $domain;
            }
        }

        return $urls;
    }
}