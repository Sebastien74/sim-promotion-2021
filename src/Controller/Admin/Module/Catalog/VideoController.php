<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Video;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * VideoController
 *
 * Catalog Video management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/videos", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Video $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class VideoController extends AdminController
{
    protected $class = Video::class;

    /**
     * Position Video
     *
     * @Route("/position/{catalogvideo}", methods={"GET", "POST"}, name="admin_catalogvideo_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        /** @var Video $video */
        $video = $this->entityManager->getRepository($this->class)->find($request->get('catalogvideo'));
        $video->setPosition($request->get('position'));

        $this->entityManager->persist($video);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Delete Video
     *
     * @Route("/delete/{catalogvideo}", methods={"DELETE"}, name="admin_catalogvideo_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}