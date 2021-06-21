<?php

namespace App\Twig\Content;

use App\Entity\Module\Menu\Link;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Media\Media;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Service\Content\MenuService;
use App\Twig\Core\WebsiteRuntime;
use App\Twig\Translation\i18nRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * MenuRuntime
 *
 * @property i18nRuntime $i18nExtension
 * @property RouterInterface $router
 * @property Request $request
 * @property MenuService $menuService
 * @property EntityManagerInterface $entityManager
 * @property WebsiteRuntime $websiteRuntime
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuRuntime implements RuntimeExtensionInterface
{
    private $i18nExtension;
    private $router;
    private $request;
    private $menuService;
    private $entityManager;
    private $websiteRuntime;

    /**
     * MenuRuntime constructor.
     *
     * @param i18nRuntime $i18nExtension
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param MenuService $menuService
     * @param EntityManagerInterface $entityManager
     * @param WebsiteRuntime $websiteRuntime
     */
    public function __construct(
        i18nRuntime $i18nExtension,
        RouterInterface $router,
        RequestStack $requestStack,
        MenuService $menuService,
        EntityManagerInterface $entityManager,
        WebsiteRuntime $websiteRuntime)
    {
        $this->i18nExtension = $i18nExtension;
        $this->router = $router;
        $this->request = $requestStack->getMasterRequest();
        $this->menuService = $menuService;
        $this->entityManager = $entityManager;
        $this->websiteRuntime = $websiteRuntime;
    }

	/**
	 * Get link infos
	 *
	 * @param array $link
	 * @param array $tree
	 * @return array
	 * @throws NonUniqueResultException
	 */
    public function linkMenu(array $link, array $tree = []): array
    {
        $i18n = $link['i18n'];
        $url = $this->getUrl($i18n);
        $path = $this->getPath($i18n, $url);
        $color = $this->getColor($link);
        $btnColor = $this->getBtnColor($link);

        return [
            'i18n' => $i18n,
            'newTab' => $i18n['newTab'],
            'externalLink' => $i18n['externalLink'],
            'link' => $link,
            'title' => $this->getTitle($link, $i18n),
            'subTitle' => $this->getPlaceholder($link, $i18n),
            'url' => $url,
            'path' => $path,
            'online' => $this->isOnline($url, $i18n),
            'active' => $this->isActive($path),
            'media' => $this->getMedia($link),
            'color' => $color,
            'bgColor' => $this->getBackground($link),
            'btnColor' => $btnColor,
            'class' => $this->getClass($color, $btnColor),
            'children' => $this->getChildren($link, $tree),
        ];
    }

    /**
     * Get mains Menu[]
     *
     * @param Website $website
     * @param array $url
     * @return array
     */
    public function allMenus(Website $website, array $url = []): array
    {
        return $this->menuService->getAll($website, $url);
    }

    /**
     * Get mains Menu[]
     *
     * @param Website $website
     * @param array $url
     * @return array
     */
    public function mainMenus(Website $website, array $url = []): array
    {
        return $this->menuService->getMains($website, $url);
    }

    /**
     * Get sub-navigation of page
     *
     * @param Page|null $page
     * @param string|null $locale
     * @return mixed
     */
    public function subNavigation(Page $page = NULL, string $locale = NULL)
    {
        if ($page instanceof Page) {

            $locale = !$locale ? $this->request->getLocale() : $locale;
            $subNavigation = $this->entityManager->getRepository(Page::class)->findOnlineAndLocaleByParent($page, $locale);

            if (!$subNavigation && $page->getParent() instanceof Page) {
                $subNavigation = $this->entityManager->getRepository(Page::class)->findOnlineAndLocaleByParent($page->getParent(), $locale);
            }

            return $subNavigation;
        }
    }

	/**
	 * Get title
	 *
	 * @param array $link
	 * @param array $i18n $i18n
	 * @return string|null
	 */
    private function getTitle(array $link, array $i18n): ?string
    {
        return $i18n['title'] ?: $link['adminName'];
    }

	/**
	 * Get placeholder for sub-title
	 *
	 * @param array $link
	 * @param array $i18n $i18n
	 * @return string|null
	 */
    private function getPlaceholder(array $link, array $i18n): ?string
    {
        return $i18n['placeholder'] ?: NULL;
    }

	/**
	 * Get URL
	 *
	 * @param array $i18n $i18n
	 * @return array
	 */
    private function getUrl(array $i18n): array
    {
        $targetPage = $i18n['targetPage'];
        if ($targetPage) {
            return $this->i18nExtension->i18n($targetPage['urls']);
        }

        return [];
    }

	/**
	 * Get path
	 *
	 * @param array $i18n $i18n
	 * @param array $url
	 * @return string|null
	 * @throws NonUniqueResultException
	 */
    private function getPath(array $i18n, array $url = []): ?string
    {
        $targetPage = $i18n['targetPage'];
        $website = $i18n['website'];
        $websiteId = $website['id'];
        $targetPageId = $targetPage ? $targetPage['website']['id'] : NULL;
        $isIndex = $targetPage && $targetPage['isIndex'];

        if($isIndex && $websiteId === $targetPageId) {
            return '/';
        }

        if($targetPage && $websiteId !== $targetPageId) {

            $domain = $this->websiteRuntime->domain($i18n['locale'], $targetPage['website']);
            if($domain) {
                if($isIndex) {
                    return $domain;
                }
                elseif ($url) {
                    return $domain . $this->router->generate('front_index', ['url' => $url['code']]);
                }
            }
        }

        return $url ? $this->router->generate('front_index', ['url' => $url['code']]) : $i18n['targetLink'];
    }

	/**
	 * Check if is online path
	 *
	 * @param array|null $url
	 * @param array $i18n |null $i18n $i18n
	 * @return bool
	 */
    private function isOnline(array $url = NULL, array $i18n = []): bool
    {
        if (!$url && !$i18n['targetPage']) {
            return true;
        }

        if (!empty($i18n['targetPage']) && $i18n['targetPage']['infill']) {
            return true;
        }

        return $url ? $url['isOnline'] : false;
    }

    /**
     * Check if is active path
     *
     * @param string|null $path
     * @return bool
     */
    private function isActive(string $path = NULL): bool
    {
        return $this->request->getUri() === $this->request->getSchemeAndHttpHost() . $path;
    }

	/**
	 * Check if have Media
	 *
	 * @param array $link
	 * @return array
	 */
    private function getMedia(array $link): array
	{
        if (!empty($link['mediaRelation']) && !empty($link['mediaRelation']['media']) && !empty($link['mediaRelation']['media']['filename'])) {
            return $link['mediaRelation']['media'];
        }

        return [];
    }

	/**
	 * Get color
	 *
	 * @param array $link
	 * @return string
	 */
    private function getColor(array $link): string
    {
        return $link['color'] ? 'text-' . $link['color'] : '';
    }

	/**
	 * Get background color
	 *
	 * @param array $link
	 * @return string
	 */
    private function getBackground(array $link): string
    {
        return $link['backgroundColor'] ? $link['backgroundColor'] : '';
    }

	/**
	 * Get button color
	 *
	 * @param array $link
	 * @return string
	 */
    private function getBtnColor(array $link): string
    {
        return $link['btnColor'] ? 'btn ' . $link['btnColor'] : '';
    }

    /**
     * Get class
     *
     * @param string $color
     * @param string $btnColor
     * @return string
     */
    private function getClass(string $color, string $btnColor): string
    {
        $class = '';

        if ($color) {
            $class .= $color;
            if ($btnColor) {
                $class .= ' ';
            }
        }

        if ($btnColor) {
            $class .= $btnColor;
        }

        return $class;
    }

	/**
	 * Get children
	 *
	 * @param array $link
	 * @param array $tree
	 * @return array
	 */
    private function getChildren(array $link, array $tree): array
    {
        return !empty($tree[$link['id']]) ? $tree[$link['id']] : [];
    }
}