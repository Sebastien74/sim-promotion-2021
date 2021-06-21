<?php

namespace App\Service\Content;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Layout\ActionI18n;
use App\Entity\Layout\Page;
use App\Entity\Layout\Zone;
use App\Entity\Seo\Session;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Helper\Core\InterfaceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * SitemapService
 *
 * Manage Website sitemap
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property RouterInterface $router
 * @property InterfaceHelper $interfaceHelper
 * @property SeoService $seoService
 * @property LocaleService $localeService
 * @property KernelInterface $kernel
 * @property string $locale
 * @property string $host
 * @property Website $website
 * @property Configuration $configuration
 * @property array $localesWebsites
 * @property array $urls
 * @property array $xml
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SitemapService
{
	private $entityManager;
	private $request;
	private $router;
	private $interfaceHelper;
	private $seoService;
	private $localeService;
	private $kernel;
	private $locale;
	private $host;
	private $website;
	private $configuration;
	private $localesWebsites = [];
	private $urls = [];
	private $xml = [];

	/**
	 * SitemapService constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 * @param RequestStack $requestStack
	 * @param RouterInterface $router
	 * @param InterfaceHelper $interfaceHelper
	 * @param SeoService $seoService
	 * @param LocaleService $localeService
	 * @param KernelInterface $kernel
	 */
	public function __construct(
		EntityManagerInterface $entityManager,
		RequestStack $requestStack,
		RouterInterface $router,
		InterfaceHelper $interfaceHelper,
		SeoService $seoService,
		LocaleService $localeService,
		KernelInterface $kernel)
	{
		$this->entityManager = $entityManager;
		$this->request = $requestStack->getCurrentRequest();
		$this->router = $router;
		$this->interfaceHelper = $interfaceHelper;
		$this->seoService = $seoService;
		$this->localeService = $localeService;
		$this->kernel = $kernel;
	}

	/**
	 * Get XML data
	 *
	 * @param Website $website
	 * @param string|null $locale
	 * @param bool $noTrees
	 * @param bool $force
	 * @return array
	 * @throws NonUniqueResultException
	 * @throws Exception
	 */
	public function execute(Website $website, string $locale = NULL, bool $noTrees = false, bool $force = false): array
	{
		$this->setVars($website, $locale);

		if (!$force && !$this->configuration->getSeoStatus() && !in_array($this->request->getClientIp(), $this->configuration->getAllIPS())) {
			return $this->xml;
		}

		$this->setIndex();
		$this->parseUrls();
		$this->internationalization();

		if ($website instanceof Website && $locale) {
			return $this->generateFront($noTrees);
		}

		return $this->xml;
	}

	/**
	 * Set mains vars
	 *
	 * @param Website|null $website
	 * @param string|null $locale
	 */
	public function setVars(Website $website = NULL, string $locale = NULL)
	{
		$this->host = $this->request->getHost();
		$this->locale = $locale ?: $this->request->getLocale();
		$this->website = $website ?: $this->entityManager->getRepository(Website::class)->findOneByHost($this->host, false);
		$this->configuration = $website ? $website->getConfiguration() : NULL;
		$this->urls = $this->setUrls();
		$this->localesWebsites = $this->localeService->getLocalesWebsites($this->website);
	}

	/**
	 * Set Xml Index
	 *
	 * @throws Exception
	 */
	private function setIndex()
	{
		$pageArray = $this->entityManager->getRepository(Page::class)
			->createQueryBuilder('p')
			->leftJoin('p.urls', 'u')
			->leftJoin('p.layout', 'l')
			->leftJoin('l.zones', 'z')
			->leftJoin('z.cols', 'c')
			->leftJoin('c.blocks', 'b')
			->leftJoin('b.actionI18ns', 'bai')
			->leftJoin('b.action', 'ba')
			->leftJoin('b.i18ns', 'bi')
			->andWhere('p.website = :website')
			->andWhere('p.isIndex = :isIndex')
			->setParameter('website', $this->website)
			->setParameter('isIndex', true)
			->addSelect('u')
			->addSelect('l')
			->addSelect('z')
			->addSelect('c')
			->addSelect('b')
			->addSelect('bai')
			->addSelect('ba')
			->addSelect('bi')
			->getQuery()
			->getArrayResult();

		$page = !empty($pageArray[0]) ? $pageArray[0] : NULL;

		if ($page) {

			$interface = $this->interfaceHelper->generate(Page::class);

			foreach ($this->localesWebsites as $locale => $domain) {

				if (!empty($this->localesWebsites[$locale])) {
					$this->xml['pages'][$page['id']][$locale]['url'] = $this->localesWebsites[$locale];
				}

				foreach ($page['urls'] as $url) {
					if ($url['locale'] === $locale) {
						$this->xml['pages'][$page['id']][$locale]['update'] = $this->lastUpdatePage($page, $locale);
						$this->xml['pages'][$page['id']][$locale]['urlEntity'] = $url;
					}
				}

				$this->xml['pages'][$page['id']][$locale]['interface'] = $interface;
				$this->xml['pages'][$page['id']][$locale]['entity'] = $page;
			}
		}
	}

	/**
	 * Set Xml Page
	 *
	 * @param array $page
	 * @param mixed $url
	 * @return void|array
	 * @throws Exception
	 */
	public function setPage(array $page, $url)
	{
		$urlEntity = is_object($url) && property_exists($url, 'url') ? $url->url : $url;

		if (!empty($urlEntity['code']) && $page['template'] !== 'components.html.twig') {
			$uri = $this->router->generate('front_index', ['url' => $urlEntity['code']]);
			$this->xml['pages'][$page['id']][$urlEntity['locale']] = [
				'update' => $this->lastUpdatePage($page, $urlEntity['locale']),
				'uri' => $uri,
				'url' => !empty($this->localesWebsites[$urlEntity['locale']]) ? $this->localesWebsites[$urlEntity['locale']] . $uri : NULL,
				'interface' => $url instanceof Url ? $url::getInterface() : $url->interface,
				'entity' => $page,
				'urlEntity' => $urlEntity,
			];
			return $this->xml['pages'][$page['id']][$urlEntity['locale']];
		}
	}

	/**
	 * Set all online Url
	 *
	 * @return array
	 */
	public function setUrls(): array
	{
		$urls = [];
		$metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();

		foreach ($metasData as $metaData) {

			$classname = $metaData->getName();
			$baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;

			if ($classname !== Session::class && $baseEntity && method_exists($baseEntity, 'getUrls') && method_exists($baseEntity, 'getWebsite')) {

				$interface = $this->interfaceHelper->generate($classname);
				$entities = $this->getEntities($classname, $baseEntity, true);
				$entities = $this->getEntities($classname, $baseEntity, false, $entities);

				foreach ($entities as $entity) {
					$entityArray = $entity['array'];
					$entityObject = $entity['object'];
					foreach ($entityArray['urls'] as $url) {
						if (($url['isOnline'] || (!empty($entity['infill']) && $entity['infill'])) && !$url['hideInSitemap']) {
							$urls[] = (object)['url' => $url, 'entity' => $entityArray, 'entityObject' => $entityObject, 'interface' => $interface];
						}
					}
				}
			}
		}

		return $urls;
	}

	/**
	 * Set all entities
	 *
	 * @param string $classname
	 * @param $baseEntity
	 * @param bool $hasArray
	 * @param array $entities
	 * @return array
	 */
	public function getEntities(string $classname, $baseEntity, bool $hasArray, array $entities = []): array
	{
		$queryBuilder = $this->entityManager->getRepository($classname)
			->createQueryBuilder('e')
			->leftJoin('e.website', 'w')
			->leftJoin('e.urls', 'u')
			->leftJoin('u.seo', 's')
			->leftJoin('u.indexPage', 'uip')
			->andWhere('e.website = :website')
			->andWhere('u.locale = :locale')
			->setParameter('website', $this->website)
			->setParameter('locale', $this->locale)
			->addSelect('w')
			->addSelect('u')
			->addSelect('s')
			->addSelect('uip');

		if (method_exists($baseEntity, 'getLayout')) {
			$queryBuilder->leftJoin('e.layout', 'l')
				->leftJoin('l.zones', 'z')
				->leftJoin('z.cols', 'c')
				->leftJoin('c.blocks', 'b')
				->leftJoin('b.actionI18ns', 'bai')
				->leftJoin('b.action', 'ba')
				->leftJoin('b.i18ns', 'bi')
				->addSelect('l')
				->addSelect('z')
				->addSelect('c')
				->addSelect('b')
				->addSelect('bai')
				->addSelect('ba')
				->addSelect('bi');
		}

		if (!empty($interface['indexPage'])) {
			$getter = 'get' . ucfirst($interface['indexPage']);
			if (method_exists($baseEntity, $getter)) {
				$queryBuilder->leftJoin('e.' . $interface['indexPage'], 'ip')
					->addSelect('ip');
			}
		}

		$resultMethod = $hasArray ? 'getArrayResult' : 'getResult';
		$entitiesDb = $queryBuilder->getQuery()->$resultMethod();

		foreach ($entitiesDb as $entity) {
			$entityId = $hasArray ? $entity['id'] : $entity->getId();
			$key = $hasArray ? 'array' : 'object';
			$entities[$entityId][$key] = $entity;
		}

		return $entities;
	}

	/**
	 * Parse all Urls result
	 *
	 * @throws Exception
	 */
	private function parseUrls()
	{
		foreach ($this->urls as $url) {
			$urlEntity = $url->url;
			if ($urlEntity['isIndex'] && !$urlEntity['hideInSitemap'] && !empty($this->localesWebsites[$urlEntity['locale']])) {
				$entity = $url->entity;
				$entityObject = $url->entityObject;
				$interface = $url->interface;
				if (!empty($interface['classname']) && $interface['classname'] === Page::class && !$entity['isIndex']) {
					$this->setPage($entity, $url);
				} elseif (!empty($interface['classname']) && $interface['classname'] !== Page::class) {
					$this->setAsCard($entity, $entityObject, $interface, $url);
				}
			}
		}
	}

	/**
	 * Set Xml for entity has card
	 *
	 * @param array $entity
	 * @param mixed $entityObject
	 * @param array $interface
	 * @param mixed $url
	 * @return void|array
	 * @throws Exception
	 */
	public function setAsCard(array $entity, $entityObject, array $interface, $url)
	{
		$urlEntity = is_object($url) && property_exists($url, 'url') ? $url->url : $url;

		if ($urlEntity['code']) {

			$urlInfos = $this->seoService->getAsCardUrl($urlEntity, $entityObject, $interface['classname'], true);

			if ($urlInfos) {

				$mappingGetter = $urlInfos->methodCategory;
				$lastLayoutUpdate = $mappingGetter && !empty($entity[$mappingGetter])
					? $this->lastUpdatePage($entity[$mappingGetter], $urlEntity['locale'])
					: $this->lastUpdatePage($entity, $urlEntity['locale']);
				$entityUpdate = $this->getDate($entity);
				$lastUpdate = $entityUpdate > $lastLayoutUpdate ? $entityUpdate : $lastLayoutUpdate;

				$this->xml[$interface['name']][$entity['id']][$urlEntity['locale']] = [
					'update' => $lastUpdate instanceof DateTime ? $lastUpdate->format('Y-m-d') : NULL,
					'uri' => $urlInfos->uri,
					'url' => $urlInfos->canonical,
					'interface' => $url instanceof Url ? $url::getInterface() : $url->interface,
					'entity' => $entity,
					'urlEntity' => $urlEntity
				];

				return $this->xml[$interface['name']][$entity['id']][$urlEntity['locale']];
			}
		}
	}

	/**
	 * Get last update for entity with layout
	 *
	 * @param mixed $entity
	 * @param string $locale
	 * @return string|NULL
	 * @throws Exception
	 */
	private function lastUpdatePage($entity, string $locale): ?string
	{
		$date = NULL;
		$update = $this->getDate($entity);

		if (!empty($entity['layout']) && !empty($entity['layout']['zones'])) {
			foreach ($entity['layout']['zones'] as $zone) {
				if (!empty($zone['cols'])) {
					foreach ($zone['cols'] as $col) {
						if (!empty($col['blocks'])) {
							foreach ($col['blocks'] as $block) {
								$actionI18n = $this->getI18n($block['actionI18ns'], $locale);
								if ($actionI18n && $block['action']) {
									$namespace = str_replace('/', '\\', $block['action']['entity']);
									$entityDb = !empty($namespace) && !empty($actionI18n['actionFilter'])
										? $this->entityManager->getRepository($namespace)
											->createQueryBuilder('e')
											->andWhere('e.id = :id')
											->setParameter('id', $actionI18n['actionFilter'])
											->getQuery()
											->getArrayResult() : NULL;
									$entity = !empty($entityDb[0]) ? $entityDb[0] : NULL;
									$update = $this->getDate($entity);
								} else {
									$entity = $this->getI18n($block, $locale);
									if (!empty($entity)) {
										$update = $this->getDate($entity);
									}
								}
								if (empty($date) || $update > $date) {
									$date = $update;
								}
							}
						}
					}
				}
			}
		}

		return $date instanceof DateTime ? $date->format('Y-m-d') : NULL;
	}

	/**
	 * Get i18n
	 *
	 * @param mixed $entity
	 * @param string $locale
	 * @return mixed
	 */
	private function getI18n($entity, string $locale)
	{
		if (!empty($entity['i18ns'])) {
			foreach ($entity['i18ns'] as $i18n) {
				if ($i18n['locale'] == $locale) {
					return $i18n;
				}
			}
		} elseif (is_iterable($entity)) {
			foreach ($entity as $row) {
				if (is_array($row) && !empty($row['locale']) && $row['locale'] === $locale) {
					return $row;
				}
			}
		}
	}

	/**
	 * Get Date
	 *
	 * @param mixed $entity
	 * @return DateTime
	 * @throws Exception
	 */
	private function getDate($entity): DateTime
	{
		if (!empty($entity['updatedAt'])) {
			return !empty($entity['updatedAt']) ? $entity['updatedAt'] : $entity['createdAt'];
		}

		return new DateTime('01-01-1970');
	}

	/**
	 * Get I18n last update
	 *
	 * @param mixed $entity
	 * @param string $locale
	 * @return DateTime
	 * @throws Exception
	 */
	private function getI18nUpdate($entity, string $locale)
	{
		if (is_object($entity) && method_exists($entity, 'getI18ns')) {
			foreach ($entity->getI18ns() as $i18n) {
				if ($i18n->getLocale() === $locale) {
					$dateTime = !empty($i18n->getUpdatedAt()) ? $i18n->getUpdatedAt() : $i18n->getCreatedAt();
					return $dateTime instanceof DateTime ? $dateTime : new DateTime('01-01-1970');
				}
			}
		}

		$dateTime = !empty($entity->getUpdatedAt()) ? $entity->getUpdatedAt() : $entity->getCreatedAt();

		return $dateTime instanceof DateTime ? $dateTime : new DateTime('01-01-1970');
	}

	/**
	 * Format XML by Locales for locales alternate
	 *
	 * @return void
	 */
	private function internationalization(): void
	{
		$xmlLocales = [];

		/** Generate all locales groups */
		foreach ($this->localesWebsites as $locale => $domain) {
			foreach ($this->xml as $category => $urls) {
				foreach ($urls as $url) {
					$xmlLocales[$locale][] = $url;
				}
			}
		}

		/** Remove urls groups if locale not exist */
		foreach ($xmlLocales as $mainLocale => $UrlsGroups) {
			foreach ($UrlsGroups as $key => $urls) {
				if (empty($urls[$mainLocale])) {
					unset($xmlLocales[$mainLocale][$key]);
				}
			}
		}

		/** Order urls groups by locales */
		foreach ($xmlLocales as $mainLocale => $UrlsGroups) {
			foreach ($UrlsGroups as $key => $urls) {
				if (!empty($urls[$mainLocale])) {
					$localeGroup = $xmlLocales[$mainLocale][$key][$mainLocale];
					unset($xmlLocales[$mainLocale][$key][$mainLocale]);
					$group = $xmlLocales[$mainLocale][$key];
					$xmlLocales[$mainLocale][$key] = [$mainLocale => $localeGroup] + $group;
				}
			}
		}

		$defaultLocale = $this->configuration->getLocale();

		if (!empty($xmlLocales[$defaultLocale])) {
			$defaultLocaleXML = $xmlLocales[$defaultLocale];
			unset($xmlLocales[$defaultLocale]);
			$xmlLocales = [$defaultLocale => $defaultLocaleXML] + $xmlLocales;
		}

		$this->xml = $xmlLocales;
	}

	/**
	 * Generate front
	 *
	 * @param bool $noTrees
	 * @return array
	 * @throws NonUniqueResultException
	 */
	private function generateFront(bool $noTrees = false): array
	{
		$result = [];

		if (!empty($this->xml[$this->locale])) {

			/** Group by entities */
			$groups = [];
			foreach ($this->xml[$this->locale] as $urls) {
				foreach ($urls as $locale => $url) {
					if ($locale === $this->locale) {
						$groups[$url['interface']['name']][$url['entity']['id']] = $url;
					}
				}
			}

			$result = !$noTrees ? $this->getTree($groups) : $groups;
		}

		return $result;
	}

	/**
	 * Get Tree of Entities
	 *
	 * @param array $groups
	 * @return array
	 * @throws NonUniqueResultException
	 */
	private function getTree(array $groups): array
	{
		$trees = [];

		foreach ($groups as $group) {
			foreach ($group as $info) {
				if (!method_exists($info['entity'], 'getParent')) {
					$trees[$info['interface']['name']][$info['entity']['position']] = $info;
					ksort($trees[$info['interface']['name']]);
				} elseif (!empty($info['urlEntity'])) {
					$parent = $info['entity']['parent'] ? $info['entity']['parent']['id'] : 'main';
					$info['seo'] = $this->seoService->execute($info['urlEntity'], $info['entity']);
					$trees[$info['interface']['name']][$parent][$info['entity']['position']] = $info;
					ksort($trees[$info['interface']['name']][$parent]);
				}
			}
		}

		return $trees;
	}
}