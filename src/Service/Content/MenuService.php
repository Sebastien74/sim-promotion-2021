<?php

namespace App\Service\Content;

use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use App\Entity\Seo\Url;
use App\Repository\Module\Menu\LinkRepository;
use App\Repository\Module\Menu\MenuRepository;
use App\Service\Core\TreeService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * MenuService
 *
 * To get menu
 *
 * @property MenuRepository $menuRepository
 * @property LinkRepository $linkRepository
 * @property TreeService $treeService
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuService
{
    private $menuRepository;
    private $linkRepository;
    private $treeService;
    private $request;

    /**
     * MenuService constructor.
     *
     * @param MenuRepository $menuRepository
     * @param LinkRepository $linkRepository
     * @param TreeService $treeService
     * @param RequestStack $requestStack
     */
    public function __construct(MenuRepository $menuRepository, LinkRepository $linkRepository, TreeService $treeService, RequestStack $requestStack)
    {
        $this->menuRepository = $menuRepository;
        $this->linkRepository = $linkRepository;
        $this->treeService = $treeService;
        $this->request = $requestStack->getMasterRequest();
    }

	/**
	 * Execute service
	 *
	 * @param Website $website
	 * @param array|null $url
	 * @param int|string|null $filter
	 * @param array $menu
	 * @return object
	 */
    public function execute(Website $website, array $url = [], $filter = NULL, array $menu = [])
    {
        if (!$menu) {
            $menu = $this->menuRepository->findOneByFilter($website, $filter, $this->request->getLocale());
        }

        $links = $menu ? $this->linkRepository->findByMenuAndLocale($menu, $this->request->getLocale()) : [];
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();

        return (object)[
            'template' => 'front/' . $template . '/actions/menu/view.html.twig',
            'entity' => $menu,
            'arguments' => [
                'configuration' => $configuration,
                'website' => $website,
                'websiteTemplate' => $template,
                'menu' => $menu,
                'url' => $url,
                'tree' => $this->treeService->execute($links)
            ]
        ];
    }

	/**
	 * Get mains menus
	 *
	 * @param Website $website
	 * @param array|null $url
	 * @return array
	 */
    public function getMains(Website $website, array $url = []): array
	{
        $main = $this->menuRepository->findMain($website, $this->request->getLocale());
        $footer = $this->menuRepository->findFooter($website, $this->request->getLocale());

        return [
            'main' => $this->execute($website, $url, NULL, $main),
            'footer' => $this->execute($website, $url, NULL, $footer)
        ];
    }

	/**
	 * Get all menus
	 *
	 * @param Website $website
	 * @param array|null $url
	 * @return array
	 */
    public function getAll(Website $website, array $url = []): array
    {
        $response = [];
        $menus = $this->menuRepository->findOptimized($website, $this->request->getLocale());

        foreach ($menus as $menu) {
            $code = $menu['isMain'] ? 'main' : ($menu['isFooter'] ? 'footer' : $menu['slug']);
            $response[$code] = $this->execute($website, $url, NULL, $menu);
        }

        return $response;
    }
}