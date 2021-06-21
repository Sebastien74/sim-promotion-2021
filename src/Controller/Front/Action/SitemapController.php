<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Service\Content\SitemapService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SitemapController
 *
 * Front Sitemap renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SitemapController extends FrontController
{
    /**
     * View
     *
     * @param Request $request
     * @param SitemapService $sitemapService
     * @param Block|null $block
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(Request $request, SitemapService $sitemapService, Block $block = NULL): Response
    {
        $website = $this->getWebsite($request);
        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();

        return $this->cache('front/' . $websiteTemplate . '/actions/sitemap/view.html.twig', $block, [
            'trees' => $sitemapService->execute($website, $request->getLocale(), false, true),
            'websiteTemplate' => $websiteTemplate,
        ], $configuration->getFullCache());
    }
}