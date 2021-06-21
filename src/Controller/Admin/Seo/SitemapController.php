<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Service\Content\SitemapService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SiteMapController
 *
 * SEO sitemap management
 *
 * @Route("/admin-%security_token%/{website}/seo/sitemap")
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SitemapController extends AdminController
{
    /**
     * Index archive
     *
     * @Route("/render", name="admin_seo_sitemap")
     *
     * @param SitemapService $sitemapService
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function sitemap(SitemapService $sitemapService)
    {
        $this->disableProfiler();

        return $this->cache('admin/page/seo/sitemap.html.twig', [
            'xml' => $sitemapService->execute()
        ]);
    }
}