<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Map\Map;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Map\MapRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MapController
 *
 * Front Map renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MapController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/map/view/{filter}", methods={"GET"}, name="front_map_view", schemes={"%protocol%"})
     *
     * @param MapRepository $mapRepository
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(MapRepository $mapRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Map $map */
        $map = $mapRepository->findOneByFilter($website, $filter);

        if (!$map) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $map;
        $entity->setUpdatedAt($map->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/map/view.html.twig', $entity, [
            'websiteTemplate' => $template,
            'website' => $website,
            'map' => $map,
            'block' => $block,
            'categories' => $this->getCategories($map)
        ], $configuration->getFullCache());
    }

    /**
     * Get Categories
     *
     * @param Map $map
     * @return array
     */
    private function getCategories(Map $map)
    {
        $categories = [];

        foreach ($map->getPoints() as $point) {
            foreach ($point->getCategories() as $category) {
                $categories[] = $category;
            }
        }

        return $categories;
    }
}