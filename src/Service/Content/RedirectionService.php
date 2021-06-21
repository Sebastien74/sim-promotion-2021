<?php

namespace App\Service\Content;

use App\Entity\Api\Api;
use App\Entity\Api\Google;
use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Seo\Redirection;
use App\Repository\Api\ApiRepository;
use App\Repository\Core\DomainRepository;
use App\Repository\Core\WebsiteRepository;
use App\Repository\Seo\RedirectionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * RedirectionService
 *
 * Front redirection management
 *
 * @property array IPS_DEV
 *
 * @property WebsiteRepository $websiteRepository
 * @property DomainRepository $domainRepository
 * @property ApiRepository $apiRepository
 * @property RedirectionRepository $redirectionRepository
 * @property KernelInterface $kernel
 * @property RouterInterface $router
 * @property string $protocol
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RedirectionService
{
	private const IPS_DEV = ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'];

	private $websiteRepository;
	private $domainRepository;
	private $apiRepository;
	private $redirectionRepository;
	private $kernel;
	private $router;
	private $protocol;

    /**
     * FrontListener constructor.
     *
     * @param WebsiteRepository $websiteRepository
     * @param DomainRepository $domainRepository
     * @param ApiRepository $apiRepository
     * @param RedirectionRepository $redirectionRepository
     * @param KernelInterface $kernel
     * @param RouterInterface $router
     */
	public function __construct(
		WebsiteRepository $websiteRepository,
		DomainRepository $domainRepository,
		ApiRepository $apiRepository,
		RedirectionRepository $redirectionRepository,
		KernelInterface $kernel,
		RouterInterface $router)
	{
		$this->websiteRepository = $websiteRepository;
		$this->domainRepository = $domainRepository;
		$this->apiRepository = $apiRepository;
		$this->redirectionRepository = $redirectionRepository;
		$this->kernel = $kernel;
		$this->router = $router;
		$this->protocol = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])] . '://';
	}

	/**
	 * To execute service
	 *
	 * @param Request $request
	 * @return array
	 * @throws NonUniqueResultException
	 */
	public function execute(Request $request): array
	{
		$session = new Session();
		$website = $session->get('frontWebsite');
		$websiteObject = $session->get('frontWebsiteObject');
		$configuration = NULL;
		$routeName = $request->get('_route');
		$domainRedirection = false;
		$urlRedirection = false;
		$inBuild = false;
		$projectInit = $this->projectInit($request);
		$host = $request->getHost();

		if (!$_POST && !$projectInit && $routeName !== 'app_new_website_project' && $routeName !== 'app_new_website_yarn_install') {
			$website = is_array($website) ? $website : $this->websiteRepository->findOneByHost($host, false, true);
			$websiteObject = $websiteObject instanceof Website ? $websiteObject : $this->websiteRepository->findOneByHost($host);
			$configuration = $website['configuration'] ?: NULL;
			if ($configuration) {
				$domain = $this->getDomain($configuration, $host);
				$locale = $domain ? $domain['locale'] : $configuration['locale'];
				$domainRedirection = $this->domainRedirection($request, $website, $configuration, $locale, $domain);
				$urlRedirection = $this->urlRedirection($request, $website, $locale);
                $inBuild = $this->inBuild($request, $configuration);
                $this->checkEnvironment($request, $website, $domain);
			}
		}

		return [
			'website' => $website,
			'websiteObject' => $websiteObject,
			'domainRedirection' => $domainRedirection,
			'urlRedirection' => $urlRedirection,
			'banRedirection' => $this->inBan($request, $configuration),
			'inBuild' => $inBuild,
			'projectInit' => $projectInit
		];
	}

	/**
	 * To get current Domain
	 *
	 * @param array $configuration
	 * @param string $host
	 * @return array|null
	 */
	private function getDomain(array $configuration, string $host): ?array
	{
		$domain = NULL;

		foreach ($configuration['domains'] as $configurationDomain) {
			if ($configurationDomain['name'] === $host) {
				$domain = $configurationDomain;
				break;
			}
		}

		return $domain ?: $this->domainRepository->findByNameArray($host);
	}

    /**
     * To redirect Website Domain if not defined has default
     *
     * @param Request $request
     * @param array $website
     * @param array $configuration
     * @param string $locale
     * @param array|null $domain
     * @return bool|string
     */
	private function domainRedirection(Request $request, array $website, array $configuration, string $locale, array $domain = NULL)
	{
		$redirection = false;
		$request->getSession()->set('front_current_domain', $domain);

		if (!$domain || !$domain['hasDefault']) {

			$defaultDomain = $this->domainRepository->findOneBy([
				'locale' => $locale,
				'configuration' => $configuration['id'],
				'hasDefault' => true
			]);

			$request->getSession()->set('front_current_domain', $defaultDomain);

			if ($defaultDomain && !preg_match('/\/uploads\/' . $website['uploadDirname'] . '/', $request->getUri())) {
				$domainName = preg_match('/http/', $defaultDomain->getName()) ? $defaultDomain->getName() : $this->protocol . $defaultDomain->getName();
				$redirection = rtrim($domainName . $request->getRequestUri(), '/');
			}
		}

		return $redirection;
	}

    /**
     * To redirect Url
     *
     * @param Request $request
     * @param array $website
     * @param string $locale
     * @return bool|string
     */
	private function urlRedirection(Request $request, array $website, string $locale)
	{
		$redirection = false;
		$websiteId = $website['id'];
		$redirections = $this->redirectionRepository->findForFront($request);

		foreach ($redirections as $redirectionDb) {
			$websiteIdRedirection = $redirectionDb['website']['id'];
			$localeRedirection = $redirectionDb['locale'];
			$old = $redirectionDb['old'];
			if ($websiteIdRedirection === $websiteId && $localeRedirection === $locale && $old === $request->getUri()
                || $websiteIdRedirection === $websiteId && $localeRedirection === $locale && $old === $request->getRequestUri()) {
				$redirection = $redirectionDb['new'];
				break;
			} elseif ($localeRedirection === $locale) {
                $redirection = $redirectionDb['new'];
			}
		}

		return $redirection !== $this->protocol ? $redirection : false;
	}

    /**
     * Check if Website is in build
     *
     * @param Request $request
     * @param mixed $configuration
     * @return bool|string
     */
	public function inBuild(Request $request, $configuration)
	{
		$isObject = $configuration instanceof Configuration;
		$ipsDev = $isObject ? $configuration->getIpsDev() : $configuration['ipsDev'];
		$ipsCustomer = $isObject ? $configuration->getIpsCustomer() : $configuration['ipsCustomer'];
		$onlineStatus = $isObject ? $configuration->getOnlineStatus() : $configuration['onlineStatus'];

		$response = false;
		$routeName = $request->get('_route');
		$envIPS = !empty($_ENV['MAINTENANCE_ALLOWED_IPS']) ? $_ENV['MAINTENANCE_ALLOWED_IPS'] : [];
		$IPS = array_unique(array_merge(self::IPS_DEV, $ipsDev, $ipsCustomer, $envIPS));
		$allowed = in_array(@$_SERVER['REMOTE_ADDR'], $IPS, true);
		$inMaintenance = !empty($_ENV['UNDER_MAINTENANCE']) && $_ENV['UNDER_MAINTENANCE'];
		$checkIPS = !empty($_ENV['MAINTENANCE_CHECK_IPS']) && $_ENV['MAINTENANCE_CHECK_IPS'];
		$sessionMaintenance = $inMaintenance && !$checkIPS;
		$requestPreview = $request->get('preview');

		if ($routeName !== 'website_in_build' && !$onlineStatus && !$allowed
			|| $routeName !== 'website_in_build' && $sessionMaintenance) {
			$response = $this->router->generate('website_in_build');
		} elseif ($routeName === 'website_in_build' && $onlineStatus && !$requestPreview && !$sessionMaintenance
			|| $routeName === 'website_in_build' && $allowed && !$requestPreview && !$sessionMaintenance) {
			$response = $this->router->generate('front_index');
		}

		return $response;
	}

	/**
	 * Check if Website is project initialization
	 *
	 * @param Request $request
	 * @return string|null
	 */
	public function projectInit(Request $request): ?string
	{
		$filesystem = new Filesystem();
		$envDirname = $this->kernel->getProjectDir() . '/.env';
		$envDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $envDirname);

		if (!$filesystem->exists($envDirname) && in_array(@$_SERVER['REMOTE_ADDR'], self::IPS_DEV, true)) {
			$routeName = $request->get('_route');
			if ($routeName !== 'app_new_website_project' && !preg_match('/_wdt/', $request->getUri()) && !preg_match('/favicon.ico/', $request->getUri())
				&& $routeName !== 'website_in_build' && $routeName !== 'app_new_website_yarn_install') {
				return $this->router->generate('app_new_website_project', [
					'_locale' => $request->getLocale(),
					'step' => 'configuration'
				]);
			}
		}

		return NULL;
	}

	/**
	 * Check if current user is ban
	 *
	 * @param Request $request
	 * @param array|null $configuration
	 * @return string|null
	 */
	public function inBan(Request $request, array $configuration = NULL): ?string
	{
		if ($configuration) {

			$ip = $request->getClientIp();
			$ipsBanConfiguration = $configuration['ipsBan'];

			$ipsBan = [];
			if (is_array($ipsBanConfiguration)) {
				foreach ($ipsBanConfiguration as $ip) {
					$matches = explode(',', $ip);
					foreach ($matches as $match) {
						$ipsBan[] = $match;
					}
				}
			}

			if (in_array($ip, $ipsBan)) {
				return $request->getSchemeAndHttpHost() . '/denied.php';
			}
		}

		return NULL;
	}

    /**
     * Check environment configuration
     *
     * @param Request $request
     * @param array $website
     * @param array|null $domain
     */
	private function checkEnvironment(Request $request, array $website, array $domain = NULL)
	{
		$message = '';

		if ($_ENV['APP_ENV_NAME'] === 'prod') {

			/** Check domain */
			if (!$domain) {
				$message .= "Vous devez configurer un domaine pour ce site. <br>";
			}

			/** Check analytics */
			$api = !empty($website['api']['id']) ? $this->apiRepository->findByArray($website['api']['id']) : [];
			if (!empty($api['google'])) {
				$analytics = false;
				foreach ($api['google']['googleI18ns'] as $i18n) {
					if ($i18n['locale'] === $request->getLocale()) {
						if ($i18n['analyticsUa'] || $i18n['tagManagerKey']) {
							$analytics = true;
							break;
						}
					}
				}
				if (!$analytics) {
					$message .= "Vous devez configurez Analytics <br>";
				}

			} else {
				$message .= "Vous devez configurez Analytics <br>";
			}
		}

		if ($message) {
			$request->getSession()->set('config_error', rtrim($message, '<br>'));
		} else {
			$request->getSession()->remove('config_error');
		}
	}
}