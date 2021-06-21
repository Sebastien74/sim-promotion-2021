<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Form\Manager\Media\MediaManager;
use App\Form\Type\Media\MediaUploadType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UploadController
 *
 * Media upload management
 *
 * @Route("/admin-%security_token%/{website}/medias/upload", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UploadController extends AdminController
{
    /**
     * Medias Uploader
     *
     * @Route("/uploader/{entityNamespace}/{entityId}", methods={"GET", "POST"}, name="admin_medias_uploader")
     *
     * @param Request $request
     * @param Website $website
     * @param string|null $entityNamespace
     * @param int|null $entityId
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function uploader(Request $request, Website $website, string $entityNamespace = NULL, int $entityId = NULL)
    {
        $entity = $website;
        if ($entityNamespace && $entityId) {
            $entity = $this->entityManager->getRepository(urldecode($entityNamespace))->find($entityId);
        }

        $form = $this->createForm(MediaUploadType::class, $entity, [
            'data_class' => $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaManager = $this->subscriber->get(MediaManager::class);
            $mediaManager->post($form, $website);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true, 'form' => $form['medias']]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $errors = '';
            foreach ($form['medias']['uploadedFile']->getErrors() as $error) {
                $errors .= $error->getMessage() . '</br>';
            }
            return new JsonResponse(['success' => false, 'errors' => rtrim($errors, '</br>')]);
        }

        return $this->cache('admin/core/form/dropzone.html.twig', [
            'form' => $form->createView(),
            'entityNamespace' => $entityNamespace,
            'entityId' => $entityId,
        ]);
    }

    /**
     * File downloader
     *
     * @Route("/download/{fileDirname}", methods={"GET"}, name="admin_medias_downloader")
     *
     * @param Request $request
     * @param string $fileDirname
     * @return BinaryFileResponse|Response
     */
    public function downloader(Request $request, string $fileDirname)
    {
        $mimeTypes = ['csv' => 'text/csv'];
        $fileDirname = $this->kernel->getProjectDir() . '/public/' . ltrim(urldecode($fileDirname), '/');
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
        $filesystem = new Filesystem();

        if ($filesystem->exists($fileDirname)) {
            $file = new File($fileDirname);
            $response = new Response(file_get_contents($file));
            $mimeType = !empty($mimeTypes[$file->getExtension()]) ? $mimeTypes[$file->getExtension()] : $file->getMimeType();
            $headers = [
                'Expires' => 'Tue, 01 Jul 1970 06:00:00 GMT',
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
                'Content-Disposition' => 'attachment; filename=' . $file->getFilename(),
                'Content-Type' => $mimeType,
                'Content-Transfer-Encoding' => 'binary',
            ];
            foreach ($headers as $key => $val) {
                $response->headers->set($key, $val);
            }
            return $response;
        }

        return $this->redirect($request->headers->get('referer'));
    }
}