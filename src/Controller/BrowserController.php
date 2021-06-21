<?php

namespace App\Controller;

use App\Controller\Front\FrontController;
use App\Entity\Core\Color;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Service\Content\RobotsService;
use App\Service\Content\SitemapService;
use App\Twig\Content\ColorRuntime;
use App\Twig\Content\MediaRuntime;
use App\Twig\Translation\i18nRuntime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BrowserController
 *
 * To add action by browser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BrowserController extends FrontController
{
    /**
     * Favicon
     *
     * @Route("/favicon.{_format}",
     *          methods={"GET"},
     *          requirements={"_format" = "ico"},
     *          schemes={"%protocol%"})
     *
     * @param Request $request
     * @param MediaRuntime $mediaRuntime
     * @return BinaryFileResponse|Response
     */
    public function favicon(Request $request, MediaRuntime $mediaRuntime)
    {
        $website = $this->getWebsite($request);
        $logos = $mediaRuntime->logos($website, NULL);
        $fileDirname = !empty($logos['favicon']) ? $this->kernel->getProjectDir() . '/public' . $logos['favicon'] : NULL;
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
        $filesystem = new Filesystem();

        if (!empty($logos['favicon']) && $filesystem->exists($fileDirname)) {
            return new BinaryFileResponse($fileDirname);
        }

        $fileDirname = $this->kernel->getProjectDir() . '/public/medias/favicon.ico';
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
        if ($filesystem->exists($fileDirname)) {
            return new BinaryFileResponse($fileDirname);
        }

        return new Response();
    }

    /**
     * Robots
     *
     * @Route("/robots.{_format}",
     *          methods={"GET"},
     *          requirements={"_format" = "txt"},
     *          schemes={"%protocol%"})
     *
     * @param Request $request
     * @param RobotsService $robotsService
     * @return Response
     * @throws Exception
     */
    public function robots(Request $request, RobotsService $robotsService): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $template = 'core/analytics/robots.txt.twig';

        $cache = $this->cacheFile($template);
        if ($cache) {
            return $cache;
        }

        return $this->cache($template, NULL, [
            'robots' => $robotsService->execute($this->getWebsite($request))
        ], true);
    }

    /**
     * Web Manifest
     *
     * @Route("/site.{_format}",
     *          methods={"GET"},
     *          requirements={"_format" = "webmanifest"},
     *          schemes={"%protocol%"})
     *
     * @param Request $request
     * @param MediaRuntime $mediaRuntime
     * @param ColorRuntime $colorRuntime
     * @param i18nRuntime $i18nRuntime
     * @return BinaryFileResponse|Response
     */
    public function siteWebManifest(
        Request $request,
        MediaRuntime $mediaRuntime,
        ColorRuntime $colorRuntime,
        i18nRuntime $i18nRuntime)
    {
        $filesystem = new Filesystem();
        $website = $this->entityManager->getRepository(Website::class)->findOneByHost($request->getHost());
        $information = $website instanceof Website ? $i18nRuntime->i18n($website->getInformation()) : NULL;
        $name = $information instanceof i18n ? $information->getTitle() : NULL;
        $logos = $website instanceof Website ? $mediaRuntime->logos($website, NULL) : [];
        $theme = $website instanceof Website ? $colorRuntime->color('favicon', $website, 'webmanifest-theme') : NULL;
        $background = $website instanceof Website ? $colorRuntime->color('favicon', $website, 'webmanifest-background') : NULL;

        $icons = [];
        $uploadDirname = $website->getUploadDirname();
        $publicDir = $this->kernel->getProjectDir() . '/public';
        $files = ['android-chrome-144x144' => '144x144', 'android-chrome-192x192' => '192x192', 'android-chrome-512x512' => '512x512'];
        foreach ($files as $fileName => $size) {
            if(!empty($logos[$fileName])) {
                $fileDirname = $publicDir . $logos[$fileName];
                if ($filesystem->exists($fileDirname)) {
                    $file = $file = new File(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname));
                    $icons[] = [
                        "src" => "/uploads/" . $uploadDirname . "/" . $fileName . "." . $file->getExtension(),
                        "sizes" => $size,
                        "type" => "image/" . $file->getExtension()
                    ];
                }
            }
        }

        return new JsonResponse([
            'name' => $name,
            'short_name' => $name,
            'description' => $name,
            'icons' => $icons,
            'start_url' => $this->request->getSchemeAndHttpHost(),
            'display' => 'standalone', /** or fullscreen */
            'theme_color' => $theme instanceof Color && $theme->getIsActive() ? $theme->getColor() : '#ffffff',
            'background_color' => $background instanceof Color && $background->getIsActive() ? $background->getColor() : '#ffffff',
        ]);
    }

    /**
     * Xml
     *
     * @Route("/sitemap.{_format}",
     *          methods={"GET"},
     *          requirements={"_format" = "xml"},
     *          schemes={"%protocol%"})
     *
     * @param SitemapService $sitemapService
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function xml(SitemapService $sitemapService)
    {
        $template = 'core/analytics/sitemap.xml.twig';

        $cache = $this->cacheFile($template);
        if ($cache) {
            return $cache;
        }

        return $this->cache($template, NULL, [
            'xml' => $sitemapService->execute()
        ], true);
    }

    /**
     * Browser config
     *
     * @Route("/browserconfig.{_format}",
     *          methods={"GET"},
     *          requirements={"_format" = "xml"},
     *          schemes={"%protocol%"})
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function browserConfig(Request $request)
    {
        $template = 'core/analytics/browserconfig.xml.twig';

        $cache = $this->cacheFile($template);
        if ($cache) {
            return $cache;
        }

        return $this->cache($template, NULL, [
            'website' => $this->getWebsite($request)
        ], true);
    }

    /**
     * View
     *
     * @Route("/core/browser/ie/alert", methods={"GET"}, name="browser_ie_alert", options={"expose"=true}, schemes={"%protocol%"})
     *
     * @return JsonResponse
     */
    public function ieAlert()
    {
        ob_start('ob_gzhandler');

        return new JsonResponse(['html' => $this->renderView('core/ie-alert.html.twig')]);
    }
}