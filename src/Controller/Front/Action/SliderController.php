<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Slider\Slider;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Slider\SliderRepository;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SliderController
 *
 * Front Slider renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SliderController extends FrontController
{
    /**
     * View
     *
     * @param Request $request
     * @param SliderRepository $sliderRepository
     * @param Website $website
     * @param null|Block $block
     * @param null|string|int $filter
     * @return Response
     * @throws Exception
     */
    public function view(Request $request, SliderRepository $sliderRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Slider $slider */
        $slider = $sliderRepository->findOneByFilter($website, $filter, $request->getLocale());

        if (!$slider) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $thumbConfiguration = $this->thumbConfiguration($website, Slider::class, 'view', $slider);
        $entity = $block instanceof Block ? $block : $slider;
        $entity->setUpdatedAt($slider->getUpdatedAt());

        if (!$thumbConfiguration) {
            $thumbConfiguration = $this->thumbConfiguration($website, Slider::class, 'view', NULL);
        }

        return $this->cache('front/' . $template . '/actions/slider/view.html.twig', $entity, [
            'websiteTemplate' => $template,
            'block' => $block,
            'website' => $website,
            'thumbConfiguration' => $thumbConfiguration,
            'slider' => $slider
        ], $configuration->getFullCache());
    }
}