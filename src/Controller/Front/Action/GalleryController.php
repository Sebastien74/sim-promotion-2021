<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Gallery\Gallery;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Gallery\GalleryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * GalleryController
 *
 * Front Gallery renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GalleryController extends FrontController
{
    /**
     * View
     *
     * @param Request $request
     * @param GalleryRepository $galleryRepository
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(Request $request, GalleryRepository $galleryRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Gallery $gallery */
        $gallery = $galleryRepository->findOneByFilter($website, $filter, $request->getLocale());

        if (!$gallery) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $gallery;
        $entity->setUpdatedAt($gallery->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/gallery/view.html.twig', $entity, [
            'websiteTemplate' => $template,
            'website' => $website,
            'gallery' => $gallery
        ], $configuration->getFullCache());
    }
}