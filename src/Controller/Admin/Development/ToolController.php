<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Form\Type\Development\FileUrlizerType;
use App\Service\Development\FileUrlizerService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ToolController
 *
 * Webmaster tools
 *
 * @Route("/admin-%security_token%/development", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ToolController extends AdminController
{
    /**
     * Tools view
     *
     * @Route("/tools", methods={"GET"}, name="admin_tools")
     *
     * @return Response
     * @throws Exception
     */
    public function tools()
    {
        return $this->cache('admin/page/development/tools.html.twig');
    }

    /**
     * Tools view
     *
     * @Route("/clear-sessions", methods={"GET"}, name="admin_clear_sessions")
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function clearSession(Request $request)
    {
        $matches = ['_route', 'last_', '_csrf', '_security', '_locale'];
        $session = $request->getSession();

        foreach ($session->all() as $name => $sessionRequest) {

            $remove = true;
            foreach ($matches as $match) {
                if (preg_match('/' . $match . '/', $name)) {
                    $remove = false;
                }
            }

            if ($remove) {
                $session->remove($name);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * To rename files
     *
     * @Route("/files-rename", methods={"GET", "POST"}, name="admin_file_rename_tool")
     *
     * @param Request $request
     * @param FileUrlizerService $fileUrlizer
     * @return Response
     */
    public function fileRename(Request $request, FileUrlizerService $fileUrlizer)
    {
        $form = $this->createForm(FileUrlizerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !empty($form->getData()['files'])) {

            $files = $form->getData()['files'];
            $zipName = $fileUrlizer->execute($files);

            $response = new Response(file_get_contents($zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
            $response->headers->set('Content-length', filesize($zipName));

            @unlink($zipName);

            $tmpDirname = $this->kernel->getProjectDir() . '/public/uploads/tmp/rename/';
            $tmpDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $tmpDirname);
            $filesystem = new Filesystem();
            $filesystem->remove($tmpDirname);

            return $response;
        }

        return $this->render('admin/page/development/files-urlizer.html.twig', [
            'form' => $form->createView()
        ]);
    }
}