<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Form\Manager\Media\SearchManager;
use App\Form\Type\Media\FolderType;
use App\Form\Type\Media\SearchType;
use App\Form\Type\Media\SelectFolderType;
use App\Repository\Media\FolderRepository;
use App\Repository\Media\MediaRepository;
use App\Service\Core\Uploader;
use App\Service\Development\FileUrlizerService;
use Exception;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FolderController
 *
 * Media Folder management
 *
 * @Route("/admin-%security_token%/{website}/medias/folders", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @property Folder $class
 * @property FolderType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FolderController extends AdminController
{
    protected $class = Folder::class;
    protected $formType = FolderType::class;

    /**
     * New Folder
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_folder_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        $this->entity = new $this->class();

        return parent::new($request);
    }

    /**
     * Edit Folder
     *
     * @Route("/edit/{folder}", methods={"GET", "POST"}, name="admin_folder_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Delete Folder
     *
     * @Route("/delete/{folder}", methods={"DELETE"}, name="admin_folder_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Select folder
     *
     * @Route("/select", methods={"GET", "POST"}, name="admin_folder_select")
     *
     * @return Response
     * @throws Exception
     */
    public function select()
    {
        $form = $this->createForm(SelectFolderType::class);
        return new JsonResponse(['html' => $this->renderView('admin/page/media/select-folder.html.twig', [
            'form' => $form->createView()
        ])]);
    }

    /**
     * Move in folder
     *
     * @Route("/move/{media}/{folderId}", methods={"GET"}, defaults={"folderId": null}, name="admin_folder_media_move", options={"expose"=true})
     *
     * @param FolderRepository $folderRepository
     * @param Media $media
     * @param int|null $folderId
     * @return JsonResponse
     */
    public function move(FolderRepository $folderRepository, Media $media, int $folderId = NULL)
    {
        $folder = $folderId ? $folderRepository->find($folderId) : NULL;
        $media->setFolder($folder);
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        return new JsonResponse(["success" => true]);
    }

    /**
     * Zip medias Folder
     *
     * @Route("/zip/{folder}", methods={"GET"}, name="admin_folder_zip")
     *
     * @param Request $request
     * @param MediaRepository $mediaRepository
     * @param Uploader $uploader
     * @param FileUrlizerService $fileUrlizerService
     * @param Folder $folder
     * @return Response
     */
    public function zip(
        Request $request,
        MediaRepository $mediaRepository,
        Uploader $uploader,
        FileUrlizerService $fileUrlizerService,
        Folder $folder)
    {
        $medias = $mediaRepository->findBy(['folder' => $folder]);
        $websiteDirname = $this->kernel->getProjectDir() . '/public/uploads/' . $folder->getWebsite()->getUploadDirname() . '/';
        $websiteDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $websiteDirname);
        $zipName = Urlizer::urlize($folder->getAdminName()) . '.zip';
        $tmpDirname = $this->kernel->getProjectDir() . '/public/uploads/tmp/medias-zip/';
        $tmpDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $tmpDirname);
        $session = new Session();

        if ($medias) {

            foreach ($medias as $media) {
                $fileDirname = $websiteDirname . $media->getFilename();
                $uploader->pathToUploadedFile($fileDirname, true, $tmpDirname);
            }

            $zip = $fileUrlizerService->zip($tmpDirname, $zipName);

            if ($zip) {

                $response = new Response(file_get_contents($zip));
                $response->headers->set('Content-Type', 'application/zip');
                $response->headers->set('Content-Disposition', 'attachment;filename="' . $zip . '"');
                $response->headers->set('Content-length', filesize($zip));

                @unlink($zipName);

                $filesystem = new Filesystem();
                if ($filesystem->exists($tmpDirname)) {
                    $filesystem->remove($tmpDirname);
                }

                return $response;

            } else {

                $session->getFlashBag()->add('info', $this->translator->trans('Une erreur est survenue !!', [], 'admin'));
                return $this->redirect($request->headers->get('referer'));
            }
        }

        $session->getFlashBag()->add('info', $this->translator->trans('Aucun fichier trouvé !!', [], 'admin'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Search media
     *
     * @Route("/search", methods={"GET", "POST"}, name="admin_folders_medias_search")
     *
     * @param Request $request
     * @param MediaRepository $mediaRepository
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function search(Request $request, MediaRepository $mediaRepository)
    {
        $this->disableProfiler();

        $template = 'admin/page/media/search.html.twig';
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var SearchManager $manager */
            $manager = $this->subscriber->get(SearchManager::class);
            $medias = $manager->search($form, $this->getWebsite($request));
            return new JsonResponse(["html" => $this->renderView($template, ['medias' => $medias])]);
        }

        return $this->cache($template, ['form' => $form->createView()]);
    }
}