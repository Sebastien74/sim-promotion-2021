<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use App\Entity\Seo\Session;
use App\Entity\Seo\SessionCity;
use App\Entity\Seo\SessionCountry;
use App\Entity\Seo\SessionGroup;
use App\Entity\Seo\SessionUrl;
use App\Entity\Seo\Url;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Faker\Factory;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Timezones;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use GeoIp2\Database\Reader;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * AnalyticService
 *
 * To manage analytics data
 *
 * @property ContainerInterface $container
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property KernelInterface $kernel
 * @property BrowserDetection $browserDetection
 * @property string $IP
 * @property string $userToken
 * @property bool $devMode
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AnalyticService
{
    private $container;
    private $authorizationChecker;
    private $entityManager;
    private $request;
    private $kernel;
    private $browserDetection;
    private $IP;
    private $userToken;
    private $devMode = false;

    /**
     * AnalyticsService constructor.
     *
     * @param ContainerInterface $container
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param BrowserDetection $browserDetection
     */
    public function __construct(
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        KernelInterface $kernel,
        BrowserDetection $browserDetection)
    {
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->kernel = $kernel;
        $this->browserDetection = $browserDetection;
    }

    /**
     * To execute service
     *
     * @param Url $url
     * @throws Exception
     */
    public function execute(Url $url)
    {
        $moduleActive = false;
        $slugs = ['analytics-internal', 'analytics-customer'];
        $website = $url->getWebsite();
        foreach ($website->getConfiguration()->getModules() as $module) {
            if(in_array($module->getSlug(), $slugs)) {
                $moduleActive = true;
                break;
            }
        }

        if($moduleActive) {

            $this->setIP();
            $isBot = $this->isBot();
            $allowedUri = $this->isAllowed();
            $allowedIps = $this->isAllowedIps($website);

            if (!$isBot && $allowedUri && $allowedIps || $this->isDevMode($isBot)) {
                $records = $this->getRecords();
                if (is_array($records)) {
                    $group = $this->getGroup($records);
                    $session = $group ? $this->getSession($group, $website, $records) : NULL;
                    if ($session) {
                        $this->addUrl($session, $url);
                    }
                }
            }
        }
    }

    /**
     * Set current IP
     *
     * @param bool $force
     * @return string
     */
    public function setIP(bool $force = false)
    {
        $session = $this->request->getSession();

        if (!$this->devMode) {
            $this->IP = $this->request->getClientIp();
        } elseif ($this->devMode && !empty($session->get('ANALYTICS_IP_DEV')) && !$force) {
            $this->IP = $session->get('ANALYTICS_IP_DEV');
        } elseif ($this->devMode || $this->devMode && $force) {
            $ip = long2ip(mt_rand());
            $session->set('ANALYTICS_IP_DEV', $ip);
            $this->IP = $ip;
        }

        return $this->IP;
    }

    /**
     * Set user token Session
     */
    public function setUserTokenSession()
    {
        $currentDate = new DateTime('now');
        $session = $this->request->getSession();
        $this->userToken = $session->get('TOKEN_SESSION');

        if (!$this->userToken) {
            $this->userToken = md5(crypt(random_bytes(10), 'rl')) . '-' . uniqid($currentDate->format('Ymdhi'));
            $session->set('TOKEN_SESSION', $this->userToken);
        }

        return $this->userToken;
    }

    /**
     * Check if is bot
     *
     * @return bool
     */
    private function isBot()
    {
        $bot_regex = '/BotLink|bingbot|AhrefsBot|ahoy|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|FDSE|FELIX IDE|ESI|fido|H�m�h�kki|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|wget|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|SeznamBot|mod_pagespeed|CCBot|openstat.ru\/Bot|m2e/i';
        $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

        return !$userAgent || preg_match($bot_regex, $userAgent);
    }

    /**
     * Check if is dev mode
     *
     * @param bool $isBot
     * @return bool
     */
    private function isDevMode(bool $isBot)
    {
        return !$isBot && $this->devMode && $this->isAllowedRoute();
    }

    /**
     * Check if is allowed Uri
     *
     * @return bool
     */
    private function isAllowed()
    {
        $tokenStorage = $this->container->get('security.token_storage');
        if (!empty($tokenStorage->getToken())) {
            return !empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'prod'
                && !empty($_ENV['APP_ENV_NAME']) && $_ENV['APP_ENV_NAME'] === 'prod'
                && is_object($this->authorizationChecker) && method_exists($this->authorizationChecker, 'isGranted')
                && !$this->authorizationChecker->isGranted('ROLE_ADMIN')
                && $this->isAllowedRoute();
        }
    }

    /**
     * Get disabled IPS
     *
     * @param Website $website
     * @return bool
     */
    private function isAllowedIps(Website $website)
    {
        $seoDisabledIps = $website->getSeoConfiguration()->getDisabledIps();

        $disabledIps = [];
        if (is_array($seoDisabledIps)) {
            foreach ($seoDisabledIps as $seoDisabledIp) {
                $disabledIps = array_merge($disabledIps, explode(',', $seoDisabledIp));
            }
        }
        $disabledIps = array_unique($disabledIps);

        return is_array($disabledIps) && !in_array($this->IP, $disabledIps);
    }

    /**
     * Check if is allowed route
     *
     * @return bool
     */
    private function isAllowedRoute()
    {
        if(!$this->request) {
            return false;
        }

        $patterns = ['_wdt', '_error', 'admin-', 'secure'];
        foreach ($patterns as $pattern) {
            if (preg_match('/\/' . $pattern . '/', $this->request->getRequestUri())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get location records
     *
     * @return array|bool
     */
    private function getRecords()
    {
        try {

            // https://www.maxmind.com/en/accounts/205743/geoip/downloads

            $countryReader = new Reader($this->kernel->getProjectDir() . "/bin/data/GeoIP/GeoLite2-Country.mmdb");
            $countryReader = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $countryReader);
            $countryRecords = $countryReader->country($this->IP);

            $cityReader = new Reader($this->kernel->getProjectDir() . "/bin/data/GeoIP/GeoLite2-City.mmdb");
            $cityReader = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cityReader);
            $cityRecords = $cityReader->city($this->IP);

            $asnReader = new Reader($this->kernel->getProjectDir() . "/bin/data/GeoIP/GeoLite2-ASN.mmdb");
            $asnReader = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $asnReader);
            $asnReader = $asnReader->asn($this->IP);

            $isoCode = $countryRecords->country->isoCode;
            $timezoneArray = $isoCode ? Timezones::forCountryCode($isoCode) : NULL;
            $timezoneName = is_array($timezoneArray) && $timezoneArray ? $timezoneArray[0] : NULL;
            $timezone = $timezoneName ? new \DateTimeZone($timezoneName) : NULL;
            $countryLocation = $timezone ? $timezone->getLocation() : NULL;

            return [
                'anonymize' => md5($this->IP),
                'localeOnSite' => $this->request->getLocale(),
                'timezone' => $timezoneName,
                'countryISO' => $isoCode,
                'countryName' => $countryRecords->country->name,
                'countryLatitude' => $countryLocation ? $countryLocation['latitude'] : NULL,
                'countryLongitude' => $countryLocation ? $countryLocation['longitude'] : NULL,
                'cityName' => $cityRecords->city->name,
                'cityPostalCode' => $cityRecords->postal->code,
                'cityLatitude' => $cityRecords->location->latitude,
                'cityLongitude' => $cityRecords->location->longitude
            ];
        } catch (InvalidDatabaseException $exception) {
            $this->setIP(true);
            $this->getRecords();
            return true;
        } catch (AddressNotFoundException $exception) {
            $this->setIP(true);
            $this->getRecords();
            return true;
        } catch (Exception $exception) {
            $this->setIP(true);
            $this->getRecords();
            return true;
        }
    }

    /**
     * Get SessionGroup
     *
     * @param array $records
     * @return SessionGroup|null
     */
    private function getGroup(array $records)
    {
        $group = NULL;

        if (!empty($records['anonymize'])) {

            $country = $this->getCountry($records);
            $city = $this->getCity($records, $country);
            $group = $this->entityManager->getRepository(SessionGroup::class)->findOneBy(['anonymize' => $records['anonymize']]);

            if ($city && !$group) {

                $group = new SessionGroup();
                $group->setAnonymize($records['anonymize']);
                $group->setLocaleVisit($records['localeOnSite']);
                $group->setCity($city);

                $this->entityManager->persist($group);
                $this->entityManager->flush();
            }
        }

        return $group;
    }

    /**
     * Get SessionCountry
     *
     * @param array $records
     * @return SessionCountry|null
     */
    private function getCountry(array $records)
    {
        $country = $this->entityManager->getRepository(SessionCountry::class)->findOneBy(['isoCode' => $records['countryISO']]);

        if (!$country) {

            $country = new SessionCountry();
            $country->setName($records['countryName']);
            $country->setIsoCode($records['countryISO']);
            $country->setLatitude($records['countryLatitude']);
            $country->setLongitude($records['countryLongitude']);
            $country->setTimezone($records['timezone']);

            $this->entityManager->persist($country);
            $this->entityManager->flush();
        }

        return $country;
    }

    /**
     * Get SessionCity
     *
     * @param array $records
     * @param SessionCountry $country
     * @return SessionCity|null
     */
    private function getCity(array $records, SessionCountry $country)
    {
        if ($country) {

            $city = $this->entityManager->getRepository(SessionCity::class)->findOneBy([
                'latitude' => $records['cityLatitude'], 'longitude' => $records['cityLongitude']
            ]);

            if (!$city) {

                $city = new SessionCity();
                $city->setName($records['cityName']);
                $city->setPostalCode($records['cityPostalCode']);
                $city->setLatitude($records['cityLatitude']);
                $city->setLongitude($records['cityLongitude']);
                $city->setCountry($country);

                $this->entityManager->persist($city);
                $this->entityManager->flush();
            }

            return $city;
        }
    }

    /**
     * Get User Session
     *
     * @param SessionGroup $group
     * @param Website $website
     * @param array $records
     * @return Session|null
     * @throws Exception
     */
    private function getSession(SessionGroup $group, Website $website, array $records)
    {
        $session = NULL;

        if (!empty($records['anonymize'])) {

            $userToken = $this->setUserTokenSession();
            $session = $this->entityManager->getRepository(Session::class)->findOneByDayAndGroup($this->request, $group, $website, $userToken);

            if (!$session) {

                $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? NULL : $_SERVER['HTTP_USER_AGENT'];

                $session = new Session();
                $session->setGroup($group);
                $session->setScreen($this->getScreen());
                $session->setWebsite($website);
                $session->setUserAgent($userAgent);
                $session->setTokenSession($userToken);
                $this->setCreatedAt($session);

                $this->entityManager->persist($session);
                $this->entityManager->flush();
            }
        }

        return $session;
    }

    /**
     * Get screen format
     *
     * @return string
     */
    private function getScreen()
    {
        if ($this->devMode) {
            $screens = ['desktop', 'tablet', 'mobile'];
            shuffle($screens);
            return $screens[0];
        } elseif ($this->browserDetection->isTablet()) {
            return 'tablet';
        } elseif (!$this->browserDetection->isTablet() && $this->browserDetection->isMobile()) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Add Url to User Session
     *
     * @param Session $session
     * @param Url $url
     * @throws Exception
     */
    private function addUrl(Session $session, Url $url)
    {
        $sessionUrl = new SessionUrl();
        $sessionUrl->setUrl($url);
        $sessionUrl->setRefererUri($this->request->headers->get('referer'));
        $sessionUrl->setUri($this->request->getRequestUri());
        $this->setCreatedAt($sessionUrl);
        $session->addUrl($sessionUrl);

        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }

    /**
     * Set createdAt
     *
     * @param $entity
     * @throws Exception
     */
    private function setCreatedAt($entity)
    {
        /** @var Session|SessionUrl $entity */

        $session = $this->request->getSession();
        $currentDate = new DateTime('now');
        $date = $currentDate;

        if ($this->devMode && !empty($session->get('ANALYTICS_CREATED_AT_DEV'))) {
            /** @var DateTime $sessionDate */
            $sessionDate = $session->get('ANALYTICS_CREATED_AT_DEV');
            $date = new DateTime($sessionDate->format('Y-m-d') . ' ' . $currentDate->format('H:i:s'));
            $entity->setCreatedAt($session->get('ANALYTICS_CREATED_AT_DEV'));
        } elseif ($this->devMode) {
            $faker = Factory::create();
            $fakerDate = $faker->dateTimeBetween('-14 days', '0 days');
            $date = new DateTime($fakerDate->format('Y-m-d') . ' ' . $currentDate->format('H:i:s'));
            $session->set('ANALYTICS_CREATED_AT_DEV', $date);
        }

        $entity->setCreatedAt($date);

        if ($entity instanceof Session) {
            $entity->setDay($date->format('Y-m-d'));
            $entity->setLastActivity($date);
        }
    }
}