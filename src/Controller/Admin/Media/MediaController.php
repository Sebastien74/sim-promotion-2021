<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Form\Manager\Media\MediaLibraryManager;
use App\Form\Manager\Media\MediaManager;
use App\Form\Manager\Media\ModalLibraryManager;
use App\Form\Widget\MediaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MediaController
 *
 * Media management
 *
 * @Route("/admin-%security_token%/{website}/medias", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaController extends AdminController
{
    /**
     * @Route("/library/{folder}", defaults={"folder": null}, methods={"GET", "POST"}, name="admin_medias_library")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function library(Request $request)
    {
        $this->template = 'admin/page/media/library.html.twig';
        $this->getBaseArguments($request);
        $this->disableProfiler();

        $formPositions = $this->getTreeForm($request, Folder::class);
        if ($formPositions instanceof JsonResponse) {
            return $formPositions;
        }

        $this->arguments['formPositions'] = $formPositions->createView();
        $arguments = $this->editionArguments($request);

        return $this->forward('App\Controller\Admin\AdminController::edition', $arguments);
    }

    /**
     * @Route("/edit/{media}", methods={"GET", "POST"}, name="admin_media_edit", options={"expose"=true})
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function edit(Request $request)
    {
        $this->class = Media::class;
        $this->formType = MediaType::class;
        $this->formManager = MediaLibraryManager::class;
        $this->formOptions = ['edition' => true, 'copyright' => true, 'name' => 'col-12', 'quality' => true];
        $this->template = 'admin/page/media/modal-media.html.twig';

        $website = $request->get('website');
        $media = $this->getDoctrine()->getRepository($this->class)->find($request->get('media'));
        $this->arguments['redirection'] = $this->generateUrl('admin_medias_library', [
            'website' => $website,
            'folder' => $media->getFolder() instanceof Folder ? $media->getFolder()->getId() : NULL
        ]);

        return $this->forward('App\Controller\Admin\AdminController::edition', $this->editionArguments($request));
    }

    /**
     * @Route("/modal/{options}", name="admin_medias_modal", methods={"GET", "POST"}, options={"expose"=true})
     *
     * @param Request $request
     * @param string $options
     * @return JsonResponse
     */
    public function modal(Request $request, string $options)
    {
        $options = (array)json_decode($options);
        $arguments = $this->getBaseArguments($request);
        $arguments['options'] = $options;

        foreach ($options as $name => $value) {
            $arguments[$name] = $value;
        }

        return new JsonResponse(['html' => $this->renderView('admin/page/media/modal.html.twig', $arguments)]);
    }

    /**
     * @Route("/modal/add/{options}/{media}", methods={"GET", "POST"}, options={"expose"=true}, name="admin_medias_modal_add")
     *
     * @param Request $request
     * @param Media $media
     * @param string|null $options
     * @return JsonResponse
     */
    public function modalAdd(Request $request, Media $media, string $options = NULL)
    {
        if ($media) {
            $this->subscriber->get(ModalLibraryManager::class)->add($this->getWebsite($request), $media, $options);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Get base arguments
     *
     * @param Request $request
     * @return array
     */
    private function getBaseArguments(Request $request)
    {
        $website = $this->getWebsite($request);
        $folderRepository = $this->entityManager->getRepository(Folder::class);
        $folder = $request->get('folder') ? $folderRepository->findOneByWebsite($website, $request->get('folder')) : NULL;
        $folders = $folderRepository->findByWebsite($website);
        $this->arguments['folder'] = $folder;
        $this->arguments['tree'] = $this->getTree($folders);
        $this->arguments['medias'] = $this->entityManager->getRepository(Media::class)->findByWebsiteAndFolder($website, $folder);

        return $this->arguments;
    }

    /**
     * Delete Media
     *
     * @Route("/delete/{media}", methods={"GET"}, name="admin_media_delete", options={"expose"=true})
     * @IsGranted("ROLE_DELETE")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        $this->class = Media::class;
        return parent::delete($request);
    }

    /**
     * Delete Media
     *
     * @Route("/remove/{media}", methods={"GET"}, name="admin_media_remove", options={"expose"=true})
     * @IsGranted("ROLE_DELETE")
     *
     * {@inheritdoc}
     */
    public function remove(Request $request, Media $media)
    {
        $mediaManager = $this->subscriber->get(MediaManager::class);
        $remove = $mediaManager->removeMedia($media);
        return new JsonResponse(['success' => $remove->success, 'message' => $remove->message]);
    }
}