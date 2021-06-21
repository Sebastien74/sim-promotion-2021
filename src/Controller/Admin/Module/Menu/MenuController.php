<?php

namespace App\Controller\Admin\Module\Menu;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Form\Type\Module\Menu\MenuType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MenuController
 *
 * Menu Action management
 *
 * @Route("/admin-%security_token%/{website}/menus", schemes={"%protocol%"})
 * @IsGranted("ROLE_NAVIGATION")
 *
 * @property Menu $class
 * @property MenuType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuController extends AdminController
{
    protected $class = Menu::class;
    protected $formType = MenuType::class;

    /**
     * Index Menu
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_menu_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Menu
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_menu_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Menu
     *
     * @Route("/edit/{menu}/{entitylocale}", methods={"GET", "POST"}, name="admin_menu_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->checkEntityLocale($request);
        $this->template = 'admin/page/menu/edit.html.twig';
        $menu = $this->entityManager->getRepository(Menu::class)->findArray($request->get('menu'));
        if (!$menu) {
            throw $this->createNotFoundException($this->translator->trans("Ce menu n'existe pas !!", [], 'front'));
        }

        $website = $this->entityManager->getRepository(Website::class)->find($request->get('website'));
        $pageRepository = $this->entityManager->getRepository(Page::class);
        $this->arguments['pages'] = $pageRepository->findByWebsiteAlpha($website);
        $pages = $pageRepository->findByWebsite($website);
        $this->arguments['treePages'] = $this->getTree($pages);

        $links = $this->entityManager->getRepository(Link::class)->findByMenuAndLocale($menu, $request->get('entitylocale'));
        $this->arguments['tree'] = $this->getTree($links);

        $formPositions = $this->getTreeForm($request, Link::class);
        if ($formPositions instanceof JsonResponse) {
            return $formPositions;
        }

        $this->arguments['formPositions'] = $formPositions->createView();
        $arguments = $this->editionArguments($request);

        return $this->forward('App\Controller\Admin\AdminController::edition', $arguments);
    }

    /**
     * Show Menu
     *
     * @Route("/show/{menu}/{entitylocale}", methods={"GET"}, name="admin_menu_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        $this->checkEntityLocale($request);
        return parent::show($request);
    }

    /**
     * Position Menu
     *
     * @Route("/position/{menu}", methods={"GET", "POST"}, name="admin_menu_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Menu
     *
     * @Route("/delete/{menu}", methods={"DELETE"}, name="admin_menu_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}