<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Timeline\Timeline;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Timeline\TimelineRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * TimelineController
 *
 * Front Timeline renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TimelineController extends FrontController
{
    /**
     * To display timeline
     *
     * @param TimelineRepository $timelineRepository
     * @param Website $website
     * @param Block|null $block
     * @param null $filter
     * @return string|JsonResponse|Response
     * @throws Exception
     */
    public function view(TimelineRepository $timelineRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Timeline $timeline */
        $timeline = $timelineRepository->findOneByFilter($filter);
        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();

        if (!$timeline) {
            return new Response();
        }

        $entity = $block instanceof Block ? $block : $timeline;
        $entity->setUpdatedAt($timeline->getUpdatedAt());

        return $this->cache('front/' . $websiteTemplate . '/actions/timeline/view.html.twig', $block, [
            'timeline' => $timeline,
            'thumbConfiguration' => $this->thumbConfiguration($website, Timeline::class, 'view', NULL),
            'configuration' => $configuration,
            'websiteTemplate' => $websiteTemplate,
            'website' => $website
        ], $configuration->getFullCache());
    }
}