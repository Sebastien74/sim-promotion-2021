<?php

namespace App\Twig\Translation;

use App\Entity\Core\Domain;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Entity\Information\Legal;
use App\Entity\Layout\ActionI18n;
use App\Entity\Layout\BlockType;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Twig\Core\AppRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * i18nRuntime
 *
 * @property Request $request
 * @property RouterInterface $router
 * @property AppRuntime $appExtension
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $router;
    private $appExtension;
    private $entityManager;

    /**
     * i18nRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param AppRuntime $appRuntime
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
		RequestStack $requestStack,
		RouterInterface $router,
		AppRuntime $appRuntime,
		EntityManagerInterface $entityManager)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->router = $router;
        $this->appExtension = $appRuntime;
        $this->entityManager = $entityManager;
    }

    /**
     * Get i18n by locale
     *
     * @param mixed $entity
     * @param string|null $locale
     * @return i18n|array
     */
    public function i18n($entity, string $locale = NULL)
    {
    	if($entity instanceof PersistentCollection) {
			return [];
		}

    	$isObject = is_object($entity);
    	$isArray = is_array($entity);
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $hasI18ns = $isObject && method_exists($entity, 'getI18ns') || $isArray && !empty($entity['i18ns']);
        $haveAdminName = $isObject && method_exists($entity, 'getAdminName') || $isArray && !empty($entity['adminName']);
		$entityId = $isObject ? $entity->getId() : (!empty($entity['id']) ? $entity['id'] : NULL);
		$adminName = $isObject && $haveAdminName ? $entity->getAdminName() : ($isArray && $haveAdminName ? $entity['adminName'] : NULL);
		$i18ns = $isObject ? $entity->getI18ns() : (!empty($entity['i18ns']) ? $entity['i18ns'] : []);

        if ($hasI18ns && $entityId > 0 || $hasI18ns && $haveAdminName && $adminName === 'force-i18n') {
            foreach ($i18ns as $i18n) {
				$i18nLocale = $isObject ? $i18n->getLocale() : $i18n['locale'];
                if ($i18nLocale === $locale) {
                    return $i18n;
                }
            }
        } else if(is_iterable($entity)) {
            foreach ($entity as $i18n) {
                if (is_object($entity) && method_exists($i18n, 'getLocale') && $i18n->getLocale() === $locale
					|| is_array($i18n) && !empty($i18n['locale']) && $i18n['locale'] === $locale) {
                    return $i18n;
                }
            }
        }

        return [];
    }

    /**
     * Get i18nAction by locale
     *
     * @param $entity
     * @param string|null $locale
     * @return ActionI18n|null
     */
    public function i18nAction($entity, string $locale = NULL): ?ActionI18n
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;

        if (method_exists($entity, 'getActionI18ns')) {
            foreach ($entity->getActionI18ns() as $i18n) {
                /** @var ActionI18n $i18n */
                if ($i18n->getLocale() === $locale) {
                    return $i18n;
                }
            }
        }

        return NULL;
    }

    /**
     * Get i18n Url by locale
     *
     * @param mixed $website
     * @param mixed $entity
     * @param string|null $locale
     * @param bool $object
     * @return mixed
     */
    public function i18nUrl($website, $entity, string $locale = NULL, bool $object = false)
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;
		$websiteId = is_object($website) ? $website->getId() : $website['id'];
		$urls = is_object($entity) && method_exists($entity, 'getUrls') ? $entity->getUrls()
			: (is_array($entity) && !empty($entity['urls']) ? $entity['urls'] : []);

		foreach ($urls as $url) {
			$localeUrl = is_object($url) ? $url->getLocale() : $url['locale'];
			if ($localeUrl === $locale) {
				$websiteUrl = is_object($url) ? $url->getWebsite() : $url['website'];
				$websiteUrlId = is_object($websiteUrl) ? $websiteUrl->getId() : $websiteUrl['id'];
				if ($websiteUrl && $websiteUrlId !== $websiteId) {
					return $this->i18nUrlByDomain($url, $locale);
				}
				return $object ? $url : (is_object($url) ? $url->getCode() : $url['code']);
			}
		}

        return NULL;
    }

    /**
     * Get i18n Url by locale and Domain
     *
     * @param mixed $url
     * @param string $locale
     * @return string|null
     */
    private function i18nUrlByDomain($url, string $locale): ?string
	{
		$isObject = is_object($url);
        $domainName = NULL;
        $domains = $isObject ? $url->getWebsite()->getConfiguration()->getDomains()
			: $url['website']['configuration']['domains'];

        if (count($domains)) {
            $domainName = $isObject ? $domains[0]->getName() : $domains[0]['name'];
        }

        foreach ($domains as $domain) {
			$domainLocale = $isObject ? $domain->getLocale() : $domain['locale'];
			$domainHasDefault = $isObject ? $domain->getHasDefault() : $domain['hasDefault'];
            if ($domainLocale == $locale && $domainHasDefault) {
				$domainName = $isObject ? $domain->getName() : $domain['name'];
                break;
            }
        }

		$code = $isObject ? $url->getCode() : $url['code'];

        return $this->request->getScheme() . '://' . rtrim($domainName, '/') . '/' . $code;
    }

    /**
     * Get i18n Url by locale in mainPages
     *
     * @param Website $website
     * @param string $slug
     * @param array $mainPages
     * @param string|null $locale
     * @return string|null
     */
    public function i18nMainUrl(Website $website, string $slug, $mainPages = [], string $locale = NULL)
    {
        if (empty($mainPages[$slug])) {
            return NULL;
        }

        return $this->i18nUrl($website, $mainPages[$slug], $locale);
    }

    /**
     * Get i18n Link by locale
     *
     * @param i18n|null $i18n
     * @return array|null
     */
    public function i18nLink( $i18n = NULL): ?array
    {
        if (!$i18n) {
            return NULL;
        }

        $link = NULL;
        $targetPage = $i18n->getTargetPage();

        if ($i18n->getTargetLink()) {
            $link = $i18n->getTargetLink();
        } elseif ($targetPage) {

            $page = $i18n->getTargetPage();
            $website = $page ? $page->getWebsite() : NULL;
            $targetDomain = $website ? $this->getTargetDomain($website) : NULL;
            $urlCode = $this->i18nUrl($website, $page);

            if ($urlCode && !$targetDomain) {
                $link = $this->router->generate('front_index', [
                    'url' => $targetPage->getIsIndex() ? NULL : $urlCode
                ]);
            } elseif ($targetDomain) {
                $link = $targetDomain . '/' . $urlCode;
            }
        }

        $href = $link;
        $isEmail = $this->appExtension->isEmail($link);
        if ($isEmail) {
            $href = 'mailto:' . $link;
        }

        $isPhone = !preg_match('/http/', $link) ? $this->appExtension->isPhone($link) : false;
        if ($isPhone) {
            $link = str_replace(' ', '', $link);
            $href = 'tel:' . str_replace(' ', '', $link);
        }

        return [
            'targetBlank' => $i18n->getNewTab(),
            'externalLink' => $i18n->getExternalLink(),
            'link' => $link === '/' ? $this->request->getSchemeAndHttpHost() : $link,
            'href' => $href,
            'alignment' => $i18n->getTargetAlignment(),
            'style' => preg_match('/btn/', $i18n->getTargetStyle()) ? 'btn ' . $i18n->getTargetStyle() : $i18n->getTargetStyle(),
            'label' => $i18n->getTargetLabel() ? $i18n->getTargetLabel() : NULL,
            'isEmail' => $isEmail,
            'isPhone' => $isPhone
        ];
    }

    /**
     * Get i18n MediaRelation by locale
     *
     * @param $entity
     * @param string|null $locale
     * @return MediaRelation|Media|null
     */
    public function i18nMedia($entity, string $locale = NULL)
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;

        if (method_exists($entity, 'getMediaRelations')) {

            foreach ($entity->getMediaRelations() as $mediaRelation) {

                /** @var MediaRelation $mediaRelation */
                if ($mediaRelation->getLocale() === $locale
                    && $mediaRelation->getMedia()
                    && $mediaRelation->getMedia()->getFilename()) {
                    return $mediaRelation;
                } elseif ($mediaRelation->getLocale() === $locale
                    && $mediaRelation->getMedia()
                    && $mediaRelation->getMedia()->getMediaScreens()->count() > 0) {

                    foreach ($mediaRelation->getMedia()->getMediaScreens() as $mediaScreen) {
                        if ($mediaScreen->getFilename()) {
                            return $mediaScreen;
                        }
                    }
                    return $mediaRelation;
                }
            }
        }

        return NULL;
    }

    /**
     * Get i18n MediaRelation by locale
     *
     * @param $entity
     * @param string|null $locale
     * @return MediaRelation[]
     */
    public function i18nMedias($entity, string $locale = NULL)
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $medias = [];

        if (method_exists($entity, 'getMediaRelations')) {
            foreach ($entity->getMediaRelations() as $i18n) {
                /** @var i18n $i18n */
                if ($i18n->getLocale() === $locale) {
                    $medias[] = $i18n;
                }
            }
        }

        return $medias;
    }

    /**
     * Get i18n Legal Information by locale
     *
     * @param Information $information
     * @param string|null $locale
     * @return Legal
     */
    public function i18nLegacy(Information $information, string $locale = NULL)
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $infos = [];

        foreach ($information->getLegals() as $legal) {
            /** @var Legal $legal */
            if ($legal->getLocale() === $locale) {
                return $legal;
            }
            $infos[$legal->getLocale()] = $legal;
        }

        $defaultLocale = $information->getWebsite()->getConfiguration()->getLocale();

        return !empty($infos[$defaultLocale]) ? $infos[$defaultLocale] : [];
    }

    /**
     * Find i18n by classname and id
     *
     * @param string $classname
     * @param int|null $id
     * @return string
     */
    public function findI18n(string $classname, int $id = NULL)
    {
        $entity = $this->appExtension->find($classname, $id);
        return $this->i18n($entity);
    }

    /**
     * Find i18n[] Modules
     *
     * @return array
     */
    public function i18nsModules(): array
    {
        $i18ns = [];
        $modules = $this->entityManager->getRepository(Module::class)->findForI18ns();

        foreach ($modules as $module) {
            foreach ($module->getI18ns() as $i18n) {
                $i18ns[$module->getSlug()]['entity'] = $module;
                $i18ns[$module->getSlug()][$i18n->getLocale()] = $i18n;
            }
        }

        return $i18ns;
    }

    /**
     * Find i18n[] Block Types
     *
     * @return array
     */
    public function i18nsBlockTypes(): array
    {
        $i18ns = [];
        $blocksTypes = $this->entityManager->getRepository(BlockType::class)->findForI18ns();

        foreach ($blocksTypes as $blockType) {
            foreach ($blockType->getI18ns() as $i18n) {
                $i18ns[$blockType->getSlug()]['entity'] = $blockType;
                $i18ns[$blockType->getSlug()][$i18n->getLocale()] = $i18n;
            }
        }

        return $i18ns;
    }

    /**
     * Get target domain
     *
     * @param Website $website
     * @return string|null
     */
    private function getTargetDomain(Website $website): ?string
    {
        foreach ($website->getConfiguration()->getDomains() as $domain) {

            /** @var Domain $domain */

            if ($domain->getName() === $this->request->getHost()) {
                return NULL;
            }

            if ($domain->getLocale() === $this->request->getLocale() && $domain->getHasDefault()) {
                $protocol = $this->request->isSecure() ? 'https://' : 'http://';
                return $protocol . $domain->getName();
            }
        }

        return NULL;
    }
}