<?php

namespace App\Service\Content;

use App\Entity\Layout\Layout;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Model;
use App\Entity\Seo\SeoConfiguration;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Helper\Core\InterfaceHelper;
use App\Twig\Content\LayoutRuntime;
use App\Twig\Content\MediaRuntime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use IntlDateFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * SeoService
 *
 * Manage Seo page
 *
 * @property Request $request
 * @property MediaRuntime $mediaRuntime
 * @property LayoutRuntime $layoutRuntime
 * @property EntityManagerInterface $entityManager
 * @property InterfaceHelper $interfaceHelper
 * @property ListingService $listingService
 * @property LocaleService $localeService
 * @property RouterInterface $router
 * @property string $locale
 * @property bool $inAdmin
 * @property Website $websiteObject
 * @property Layout $layout
 * @property array $interface
 * @property string $classname
 * @property array $website
 * @property array $currentWebsite
 * @property array $logos
 * @property mixed $entity
 * @property array $model
 * @property array $localesWebsites
 * @property array $indexPages
 * @property string $indexPageCode
 * @property string $canonicalPattern
 * @property array $seo
 * @property string $title
 * @property string $titleH1
 * @property string $fullTitle
 * @property string $titleSecond
 * @property array $informationI18n
 * @property string $ogTitle
 * @property string $description
 * @property bool $isHomePage
 * @property array $i18n
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoService
{
	private $request;
	private $mediaRuntime;
	private $layoutRuntime;
	private $entityManager;
	private $interfaceHelper;
	private $listingService;
	private $localeService;
	private $router;
	private $locale;
	private $inAdmin = false;
	private $websiteObject;
	private $layout;
	private $interface;
	private $classname;
	private $website;
	private $currentWebsite;
	private $logos;
	private $entity;
	private $model;
	private $localesWebsites = [];
	private $indexPageCode;
	private $canonicalPattern;
	private $seo;
	private $title;
	private $titleH1;
	private $fullTitle;
	private $titleSecond;
	private $informationI18n;
	private $ogTitle;
	private $description;
	private $isHomePage = false;
	private $i18n;

	/**
	 * SeoService constructor.
	 *
	 * @param RequestStack $requestStack
	 * @param MediaRuntime $mediaRuntime
	 * @param LayoutRuntime $layoutRuntime
	 * @param EntityManagerInterface $entityManager
	 * @param InterfaceHelper $interfaceHelper
	 * @param ListingService $listingService
	 * @param LocaleService $localeService
	 * @param RouterInterface $router
	 */
	public function __construct(
		RequestStack $requestStack,
		MediaRuntime $mediaRuntime,
		LayoutRuntime $layoutRuntime,
		EntityManagerInterface $entityManager,
		InterfaceHelper $interfaceHelper,
		ListingService $listingService,
		LocaleService $localeService,
		RouterInterface $router)
	{
		$this->request = $requestStack->getCurrentRequest();
		$this->mediaRuntime = $mediaRuntime;
		$this->layoutRuntime = $layoutRuntime;
		$this->entityManager = $entityManager;
		$this->interfaceHelper = $interfaceHelper;
		$this->listingService = $listingService;
		$this->router = $router;
		$this->localeService = $localeService;
		$this->locale = $this->request->get('entitylocale')
			? $this->request->get('entitylocale')
			: $this->request->getLocale();
		$this->inAdmin = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri());
	}

    /**
     * Execute service
     *
     * @param Url|null $url
     * @param mixed $entity
     * @param string|null $locale
     * @return false|array
     */
    public function execute(Url $url = NULL, $entity = NULL, string $locale = NULL)
	{
		if (!$url) {
			return false;
		}

		if ($locale) {
			$this->locale = $locale;
		}

        $this->websiteObject = $url->getWebsite();
        $url = $this->entityManager->getRepository(Url::class)->findArray($url->getId());
        $classname = $this->entityManager->getClassMetadata(get_class($entity))->getName();
        $this->layout = method_exists($this->entity, 'getLayout') ? $this->entity->getLayout() : NULL;
		$this->interface = $this->interfaceHelper->generate($classname);
		$this->classname = $classname;
		$this->website = $url['website'];
		$this->currentWebsite = $this->website ?: $this->entityManager->getRepository(Website::class)->findCurrent(true);
		$this->logos = $this->mediaRuntime->logos($this->website);
		$this->entity = $entity ?: $this->setEntity($url);
		$this->model = $this->getModel($url);
		$this->localesWebsites = $this->localeService->getLocalesWebsites($this->websiteObject);
//        $this->analyticService->execute($url);

		$this->setI18n();
		$this->setSeo($url);

		return $this->getResponse($url);
	}


	/**
	 * Get Model card
	 *
	 * @param array $url
	 * @return array
	 */
	private function getModel(array $url): array
	{
        $website = method_exists($this->entity, 'getWebsite') && $this->entity->getWebsite() ? $this->entity->getWebsite() : $this->websiteObject;
		return $this->entityManager->getRepository(Model::class)->findArrayByLocaleClassnameAndWebsite($url['locale'], $this->classname, $website);
	}

	/**
	 * Get response
	 *
	 * @param array $url
	 * @return array
	 */
	private function getResponse(array $url): array
	{
		$this->getInformation();
		$haveAfterDash = $this->haveAfterDash();

        return [
			'entity' => $this->entity,
			'haveIndexPage' => $this->getHaveIndexPage(),
			'url' => $url,
			'haveAfterDash' => $haveAfterDash,
			'index' => $url['isIndex'] && $this->currentWebsite['id'] == $this->website['id'] ? 'index' : 'noindex',
			'locale' => $this->locale,
			'afterDash' => $haveAfterDash ? $this->getTitleSecond() : NULL,
			'title' => $this->getTitle(),
			'titleH1' => $this->titleH1,
			'fullTitle' => $this->getFullTitle($haveAfterDash),
			'description' => $this->getDescription(),
			'author' => $this->getAuthor(),
			'footerDescription' => $this->getFooterDescription(),
			'canonical' => $this->getCanonical($url),
			'canonicalPattern' => $this->canonicalPattern,
            'createdAt' => method_exists($this->entity, 'getCreatedAt') ? $this->entity->getCreatedAt() : NULL,
            'updatedAt' => method_exists($this->entity, 'getUpdatedAt') ? $this->entity->getUpdatedAt() : NULL,
            'publishedDate' => method_exists($this->entity, 'getPublicationStart') ? $this->entity->getPublicationStart() : NULL,
			'ogTitle' => $this->getOgTitle(),
			'ogFullTitle' => $this->getOgFullTitle(),
			'ogDescription' => $this->getOgDescription(),
			'ogType' => 'website',
			'ogTwitterCard' => $this->seo ? $this->seo['metaOgTwitterCard'] : NULL,
			'ogTwitterHandle' => $this->seo ? $this->seo['metaOgTwitterHandle'] : NULL,
			'ogImage' => $this->getOgImage(),
			'microdata' => $this->getMicrodata(),
			'isHomePage' => $this->isHomePage,
			'localesAlternate' => $this->getLocalesAlternates($url)
		];
	}

	/**
	 * To set Information i18n
	 */
	private function getInformation()
	{
		$information = $this->website['information'];
		$this->informationI18n = $this->getI18n($information);
	}

	/**
	 * Check if have index Page
	 *
	 * @return bool
	 */
	private function getHaveIndexPage(): bool
	{
		return !empty($this->interface['indexPage']);
	}

	/**
	 * Get title
	 *
	 * @return string|null
	 */
	private function getTitle(): ?string
	{
		$this->titleH1 = NULL;
		$this->title = $this->seo ? $this->seo['metaTitle'] : NULL;
        $title = $this->layout ? $this->layoutRuntime->mainLayoutTitle($this->layout, NULL, true, true) : NULL;

		$this->titleH1 = !empty($title['titleForce']) && $title['titleForce'] === 1 ? $title['title'] : NULL;

        if ($this->layout) {
			if (!$this->title) {
				$this->title = !empty($title['title']) ? $title['title'] : NULL;
			}
			if (!empty($title['i18ns'])) {
				foreach ($title['i18ns'] as $i18n) {
					if ($i18n['locale'] === $this->locale) {
						if (!$this->title) {
							$this->title = $i18n['title'];
						}
						if (!$this->titleH1) {
							$this->titleH1 = $i18n['title'];
						}
					}
				}
			}
		}

		if (!$this->title && $this->model) {
			$this->title = $this->parseModel($this->entity, $this->model['metaTitle']);
		}

		if (!$this->title && $this->i18n) {
			$this->title = $this->i18n['title'];
		}

		return strip_tags(rtrim($this->title, '.'));
	}

	/**
	 * Get Full title with after dash
	 *
	 * @param bool $haveAfterDash
	 * @return string|null
	 */
	private function getFullTitle(bool $haveAfterDash): ?string
	{
		$this->fullTitle = $this->title;

		if ($this->title && $haveAfterDash) {
			$this->fullTitle = $this->title . ' - ' . $this->titleSecond;
		}

		if (!$this->fullTitle && $this->titleSecond) {
			$this->fullTitle = $this->titleSecond;
		}

		return $this->fullTitle;
	}

	/**
	 * Get title
	 *
	 * @return string|null
	 */
	private function getTitleSecond(): ?string
	{
		if ($this->seo) {
			$this->titleSecond = $this->seo['metaTitleSecond'];
		}

		if (!$this->titleSecond && $this->model) {
			$this->titleSecond = $this->parseModel($this->entity, $this->model['metaTitleSecond']);
		}

		/** Get Seo configuration */
		if (!$this->titleSecond) {
			$i18n = $this->getI18n($this->website['seoConfiguration']);
			$this->titleSecond = $i18n ? $i18n['title'] : NULL;
		}

		/** Get Website configuration */
		if (!$this->titleSecond && $this->informationI18n) {
			$this->titleSecond = $this->informationI18n['title'];
		}

		return strip_tags(rtrim($this->titleSecond, '.'));
	}

	/**
	 * Get description
	 *
	 * @return string|null
	 */
	private function getDescription(): ?string
	{
		$hasQuery = false;
		$this->description = $this->seo ? $this->seo['metaDescription'] : NULL;
		$i18n = $this->i18n;

		if (!$this->description && $this->layout) {
			$blockText = $this->layoutRuntime->layoutBlockType($this->layout, 'text');
			$i18n = $this->getI18n($blockText);
		}

		if (!$this->description && $this->model) {
			$this->description = $this->parseModel($this->entity, $this->model['metaDescription']);
		}

		if (!$this->description && $i18n) {

			if ($i18n['introduction']) {
				$this->description = $i18n['introduction'];
			}

			if (!$this->description && $i18n['body']) {
				$this->description = $i18n['body'];
			}
		}

		$result = $hasQuery && $this->description
			? substr(str_replace(["\r", "\n"], '', strip_tags($this->description)), 0, 155)
			: $this->description;

		return strip_tags(rtrim(str_replace('"', "''", $result), '.'));
	}

	/**
	 * Get author
	 *
	 * @return string|null
	 */
	private function getAuthor(): ?string
	{
		return $this->seo ? $this->seo['author'] : NULL;
	}

	/**
	 * Get footer description
	 *
	 * @return string|null
	 */
	private function getFooterDescription(): ?string
	{
		$description = $this->seo ? $this->seo['footerDescription'] : NULL;

		if (!$description && $this->model) {
			$description = $this->parseModel($this->model, $this->model['footerDescription']);
		}

		return $description;
	}

	/**
	 * Check if meta title have string after dash
	 *
	 * @return bool
	 */
	private function haveAfterDash(): bool
	{
		$result = true;

		if ($this->seo && strlen(strip_tags($this->seo['metaTitle'])) > 0) {
			$result = !$this->seo['noAfterDash'];
		} elseif (!$this->seo && $this->model || $this->seo && strlen(strip_tags($this->seo['metaTitle'])) === 0 && $this->model) {
			$result = !$this->model['noAfterDash'];
		}

		return $result;
	}

	/**
	 * Get canonical
	 *
	 * @param array $url
	 * @return string
	 */
	private function getCanonical(array $url)
	{
        $schemeAndHttpHost = !empty($this->localesWebsites[$url['locale']])
            ? $this->localesWebsites[$url['locale']]
            : $this->request->getSchemeAndHttpHost();
        $seoCanonical = $this->seo ? $this->seo['metaCanonical'] : NULL;

        $this->isHomePage = false;
        if (!$seoCanonical && $this->entity instanceof Page && $this->entity->getIsIndex()) {
            $this->isHomePage = true;
        }

        $requestUri = $this->request->getRequestUri();
        $canonical = !preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $requestUri)
            ? rtrim($schemeAndHttpHost . $requestUri, '/')
            : NULL;

        if ($this->entity instanceof Page && !$this->isHomePage) {
            $canonical = $schemeAndHttpHost . '/' . $url['code'];
        } elseif ($this->model) {
            $canonical = $this->getAsCardUrl($url, $this->entity, get_class($this->entity), true);
        }

		$canonical = is_object($canonical) && property_exists($canonical, 'canonical') ? $canonical->canonical : $canonical;
        $matches = explode('/', $canonical);
        $this->canonicalPattern = !$this->isHomePage ? str_replace(end($matches), '', $canonical) : $canonical;

        return $canonical ? ltrim($canonical, '/') : ltrim($schemeAndHttpHost, '/');
	}

	/**
	 * Get Url for entity has card
	 *
	 * @param array $url
	 * @param mixed $entity
	 * @param string $classname
	 * @param bool $hasObject
	 * @return string
	 */
	public function getAsCardUrl(array $url, $entity, string $classname, bool $hasObject = false)
	{
		$canonical = $this->request->getSchemeAndHttpHost();
		$interface = $this->interfaceHelper->generate($classname);
		$methodCategory = !empty($interface['indexPage']) ? $interface['indexPage'] : NULL;
		$listingClass = !empty($interface['listingClass']) ? $interface['listingClass'] : NULL;
		$indexPage = $url['indexPage'];
		$indexUrlCodes = $methodCategory && $listingClass ? $this->listingService->indexesPages($entity, $url['locale'], $listingClass, $classname, [$entity], $interface, true) : NULL;
		$pageUrl = !empty($indexUrlCodes[$entity->getId()]) ? $indexUrlCodes[$entity->getId()] : NULL;
		$uri = $pageUrl && !empty($this->router->getRouteCollection()->get('front_' . $interface['name'] . '_view.' . $url['locale']))
			? $this->router->generate('front_' . $interface['name'] . '_view', ['pageUrl' => $pageUrl, 'url' => $url['code']]) : NULL;

		if (!$uri) {
			$existingOnlyRoute = !empty($this->router->getRouteCollection()->get('front_' . $interface['name'] . '_view_only.' . $url['locale']));
			$uri = $existingOnlyRoute ? $this->router->generate('front_' . $interface['name'] . '_view_only', ['url' => $url['code']]) : NULL;
		}

		if ($uri) {

			$canonical = $canonical . $uri;

			if ($hasObject) {
				return (object)[
					'methodCategory' => $methodCategory,
					'indexPage' => $indexPage,
					'uri' => $uri,
					'canonical' => $canonical
				];
			}

			return $canonical;
		}
	}

	/**
	 * Get OG Image
	 *
	 * @param bool $getFirst
	 * @return string|null
	 */
	private function getOgImage(bool $getFirst = false): ?string
	{
		$mediaRelation = $this->seo ? $this->seo['mediaRelation'] : NULL;

		if ($mediaRelation) {

			$seoMedia = $mediaRelation['media'];
			$media = $seoMedia && $seoMedia['filename'] ? $seoMedia : NULL;
			$uploadDirname = $this->website['uploadDirname'];

			/** Get first image of Page [disabled by default] */
			if (!$media && $this->classname === Page::class && $getFirst) {
				$repository = $this->entityManager->getRepository(Block::class);
				$media = $repository->findMediaByLocalePage($this->entity, $this->locale);
			}

			if ($media && !preg_match('/' . $uploadDirname . '/', $media['filename'])) {
				return $this->request->getSchemeAndHttpHost() . '/uploads/' . $uploadDirname . '/' . $media['filename'];
			}

			return $media ? $this->request->getSchemeAndHttpHost() . '/uploads/' . $uploadDirname . '/' . $media['filename'] : NULL;
		}

		return NULL;
	}

	/**
	 * Get Microdata
	 *
	 * @return array
	 */
	private function getMicrodata(): array
	{
		$seoConfiguration = $this->website['seoConfiguration'];
		$seoConfigurationsLocale = $this->entityManager->createQueryBuilder()->select('s')
			->from(SeoConfiguration::class, 's')
			->leftJoin('s.i18ns', 'i')
			->andWhere('s.id = :id')
			->andWhere('i.locale = :locale')
			->setParameter('id', $seoConfiguration['id'])
			->setParameter('locale', $this->locale)
			->addSelect('i')
			->getQuery()
			->getArrayResult();
		$seoConfigurationLocale = !empty($seoConfigurationsLocale[0]) ? $seoConfigurationsLocale[0] : [];
		$i18n = $this->getI18n($seoConfigurationLocale);
		$author = $this->seo && $this->seo['author'] ? $this->seo['author'] : ($i18n ? $i18n['author'] : NULL);

		return [
			'companyType' => $i18n && $i18n['placeholder'] ? $i18n['placeholder'] : "Organization",
			'companyLogo' => !empty($this->logos['logo']) ? $this->request->getSchemeAndHttpHost() . $this->logos['logo'] : NULL,
			'companyName' => $i18n && $i18n['title'] ? $i18n['title'] : ($this->informationI18n ? $this->informationI18n['title'] : NULL),
			'author' => $author ?: ($this->informationI18n ? $this->informationI18n['title'] : NULL),
			'authorType' => $this->seo && $this->seo['authorType'] ? $this->seo['authorType'] : ($i18n && $i18n['authorType'] ? $i18n['authorType'] : 'Organization'),
			'script' => $this->seo && $this->seo['metadata'] ? $this->seo['metadata'] : NULL,
		];
	}

	/**
	 * Get OG title
	 *
	 * @param bool $displayDefault
	 * @return string|null
	 */
	private function getOgTitle(bool $displayDefault = false): ?string
	{
		$this->ogTitle = $this->seo ? $this->seo['metaOgTitle'] : NULL;

		if (!$this->ogTitle && $this->model) {
			$this->ogTitle = $this->parseModel($this->entity, $this->model['metaOgTitle']);
		}

		if ($displayDefault) {
			$this->ogTitle = $this->ogTitle ?: $this->title;
		}

		return strip_tags(rtrim($this->ogTitle, '.'));
	}

	/**
	 * Get OG title with after dash
	 *
	 * @return string|null
	 */
	private function getOgFullTitle(): ?string
	{
		return $this->ogTitle ?: $this->fullTitle;
	}

	/**
	 * Get OG description
	 *
	 * @return string|null
	 */
	private function getOgDescription(): ?string
	{
		$ogDescription = $this->seo ? $this->seo['metaOgDescription'] : NULL;

		if (!$ogDescription && $this->model) {
			$ogDescription = $this->parseModel($this->entity, $this->model['metaOgDescription']);
		}

		$result = $ogDescription ? $ogDescription : $this->description;

		return strip_tags(rtrim(str_replace('"', "''", $result), '.'));
	}

	/**
	 * Set i18n
	 */
	private function setI18n()
	{
        if (method_exists($this->entity, 'getI18ns')) {
            $entityLocale = $this->entityManager->createQueryBuilder()->select('e')
                ->from($this->classname, 'e')
                ->leftJoin('e.i18ns', 'i')
                ->andWhere('e.id = :id')
                ->andWhere('i.locale = :locale')
                ->setParameter('id', $this->entity->getId())
                ->setParameter('locale', $this->locale)
                ->addSelect('i')
                ->getQuery()
                ->getArrayResult();
            $this->i18n = $this->getI18n($entityLocale);
        } elseif (method_exists($this->entity, 'getI18n')) {
            $this->i18n = $this->entity->getI18n();
        }
	}

	/**
	 * Get i18n
	 *
	 * @param mixed $entity
	 * @return array|null
	 */
	private function getI18n($entity = NULL): ?array
	{
		if (!$entity) {
			return NULL;
		}

		$isObject = is_object($entity);
		$i18ns = $isObject && method_exists($entity, 'getI18ns')
            ? $entity->getI18ns() : (is_array($entity) && !empty($entity['i18ns']) ? $entity['i18ns'] : []);

		if ($i18ns) {
			foreach ($i18ns as $i18n) {
			    $i18nLocale = $isObject ? $i18n->getLocale() : $i18n['locale'];
				if ($i18nLocale === $this->locale) {
					return $i18n;
				}
			}
		}

		return NULL;
	}

	/**
	 * Set Seo
	 *
	 * @param array $url
	 */
	private function setSeo(array $url): void
	{
		$this->seo = $url['seo'];
	}

	/**
	 * Set Entity
	 *
	 * @param array $url
	 * @return array
	 */
	private function setEntity(array $url = []): array
	{
		$metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();

		foreach ($metasData as $metaData) {

			$classname = $metaData->getName();
			$baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;

			if ($baseEntity && method_exists($baseEntity, 'getUrls')) {

				$queryBuilder = $this->entityManager->createQueryBuilder()->select('e')
					->from($classname, 'e')
					->leftJoin('e.urls', 'u')
					->andWhere('u.id = :id')
					->setParameter('id', $url['id'])
					->addSelect('u');

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

				$result = $queryBuilder->getQuery()
					->getArrayResult();

				if (!empty($result[0])) {
					return $result[0];
				}
			}
		}

		return [];
	}

	/**
	 * Parse model
	 *
	 * @param $entity
	 * @param string|null $string
	 * @return string|null
	 */
	private function parseModel($entity, string $string = NULL): ?string
    {
		if (!$string) {
			return NULL;
		}

		$stringResult = NULL;

		if (preg_match_all("/\[([0-9a-zA-Z\.]+)\]/", $string, $matches)) {

			foreach ($matches[1] as $match) {

				$methods = explode('.', $match);
				$property = "";

				foreach ($methods as $methodSEO) {

					$method = "get" . ucfirst($methodSEO);

					if ($property instanceof PersistentCollection) {
						foreach ($property as $propertyCol) {
							if ($propertyCol instanceof i18n) {
								if ($propertyCol->getLocale() === $this->request->getLocale()) {
									$property = $propertyCol->$method();
									break;
								}
							}
						}
					}

					if (empty($property) && method_exists($entity, $method)) {
						$property = $entity->$method();
					} elseif (method_exists($property, $method)) {
						$property = $property->$method();
					}

					if ($property instanceof DateTime) {
						$formatter = new IntlDateFormatter($this->request->getLocale(), IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
						$formatter->setPattern('E dd.MM.yyyy');
						$property = $formatter->format($property);
					}
				}

				if (!$property instanceof PersistentCollection) {
					$stringResult = str_replace('[' . $match . ']', $property, $string);
				}
			}
		}

		return trim($stringResult) != trim($string) ? trim($stringResult) : NULL;
	}

	/**
	 * Get locales alternates URL
	 *
	 * @param array $url
	 * @return array
	 */
	private function getLocalesAlternates(array $url): array
	{
		$alternates = [];
        if (count($this->websiteObject->getConfiguration()->getAllLocales()) > 0) {
            $alternates = $this->localeService->execute($this->websiteObject, $this->entity, $url, $this->indexPageCode);
        }

		return $alternates;
	}
}