<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\CacheCommand;
use App\Command\LiipCommand;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CacheController
 *
 * To execute cache commands
 *
 * @Route("/admin-%security_token%/development/commands/cache", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CacheController extends BaseCommand
{
    /**
     * Clear cache
     *
     * @Route("/clear", methods={"GET"}, name="cache_clear", options={"expose"=true})
     *
     * @param Request $request
     * @param CacheCommand $cmd
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */
    public function clear(Request $request, CacheCommand $cmd)
    {
        if ($request->get('ajax')) {
            return new JsonResponse(['success' => true]);
        }

        $this->setFlashBag($cmd->clear(), 'cache:clear');
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Clear cache
     *
     * @Route("/clear-html", methods={"GET"}, name="cache_clear_html")
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */
    public function clearHtml(Request $request)
    {
        $website = $this->getWebsite($request);
        $filesystem = new Filesystem();
        $cacheDirname = $this->kernel->getProjectDir() . '/var/cache/';
        $cacheDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cacheDirname);
        $websiteUploadDirname = $website->getUploadDirname();
        $environments = ['prod', 'dev'];

        foreach ($environments as $environment) {
            $dirname = $cacheDirname . $environment . '/' . $websiteUploadDirname;
            if ($filesystem->exists($dirname)) {
                $filesystem->remove($dirname);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Clear Liip cache
     *
     * @Route("/liip/clear", methods={"GET"}, name="cache_liip_clear")
     *
     * @param Request $request
     * @param LiipCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function liipClear(Request $request, LiipCommand $cmd)
    {
        $filesytem = new Filesystem();
        $cacheDirname = $this->kernel->getProjectDir() . '/public/medias/webp/';
        $cacheDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cacheDirname);
        if($filesytem->exists($cacheDirname)) {
            $filesytem->remove($cacheDirname);
        }

        $this->setFlashBag($cmd->remove(), 'liip:imagine:cache:remove');
        return $this->redirect($request->headers->get('referer'));
    }
}