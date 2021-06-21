<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Faq\Faq;
use App\Entity\Module\Faq\Question;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Faq\FaqRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FaqController
 *
 * Front Faq renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FaqController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/faq/view/{filter}", methods={"GET"}, name="front_faq_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param FaqRepository $faqRepository
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(Request $request, FaqRepository $faqRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Faq $faq */
        $faq = $faqRepository->findOneByFilter($website, $filter, $request->getLocale());

        if (!$faq) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $faq;
        $entity->setUpdatedAt($faq->getUpdatedAt());

        return $this->cache('front/' . $websiteTemplate . '/actions/faq/view.html.twig', $entity, [
            'configuration' => $configuration,
            'websiteTemplate' => $websiteTemplate,
            'website' => $website,
            'thumbConfiguration' => $this->thumbConfiguration($website, Question::class, 'view'),
            'faq' => $faq
        ], $configuration->getFullCache());
    }
}