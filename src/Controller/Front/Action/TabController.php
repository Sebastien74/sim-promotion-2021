<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Tab\Content;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Tab\TabRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TabController
 *
 * Front Tab render
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TabController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/tab/view/{filter}", methods={"GET"}, name="front_tab_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param TabRepository $tabRepository
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(Request $request, TabRepository $tabRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        $tab = $tabRepository->findOneByFilter($website, $filter, $request->getLocale());

        if (!$tab) {
            return new Response();
        }
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $tab;
        $entity->setUpdatedAt($tab->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/tab/view.html.twig', $entity, [
            'tab' => $tab,
            'website' => $website,
            'websiteTemplate' => $template,
            'thumbConfiguration' => $this->thumbConfiguration($website, Content::class, 'view'),
            'tabs' => $this->getTree($tab->getContents())
        ], $configuration->getFullCache());
    }
}