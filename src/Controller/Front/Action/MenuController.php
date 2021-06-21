<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Seo\Url;
use App\Service\Content\MenuService;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MenuController
 *
 * Front Menu renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/menu/view/{filter}/{website}/{url}", methods={"GET"}, name="front_menu_view", schemes={"%protocol%"})
     *
     * @param Url $url
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws Exception
     */
    public function view(Url $url, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        $menu = $this->subscriber->get(MenuService::class)->execute($website, $url, $filter);

        if (!$menu->entity) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $entity = $block instanceof Block ? $block : $menu;
        $entity->setUpdatedAt($menu->getUpdatedAt());

        return $this->cache($menu->template, $entity, $menu->arguments, $configuration->getFullCache());
    }
}