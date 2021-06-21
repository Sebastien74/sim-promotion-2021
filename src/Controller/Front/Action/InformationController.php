<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * InformationController
 *
 * Front contact information render
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationController extends FrontController
{
    /**
     * View
     *
     * @param Website $website
     * @param Block|null $block
     * @return Response
     * @throws Exception
     */
    public function view(Website $website, Block $block = NULL)
    {
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $information = $website->getInformation();
        $entity = $block instanceof Block ? $block : $information;
        $entity->setUpdatedAt($information->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/information/view.html.twig', $entity, [
            'websiteTemplate' => $template,
            'website' => $website,
            'information' => $information
        ], $configuration->getFullCache());
    }
}