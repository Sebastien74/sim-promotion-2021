<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Module\Newsletter\Email;
use App\Entity\Core\Website;
use App\Form\Manager\Front\NewsletterManager;
use App\Form\Type\Module\Newsletter\FrontType;
use App\Repository\Module\Newsletter\CampaignRepository;
use App\Service\Core\CacheService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NewsletterController
 *
 * Front Newsletter renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewsletterController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/newsletter/view/{website}/{filter}", methods={"GET", "POST"}, name="front_newsletter_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param CampaignRepository $campaignRepository
     * @param NewsletterManager $manager
     * @param Website $website
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(
        Request $request,
        CampaignRepository $campaignRepository,
        NewsletterManager $manager,
        Website $website,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Campaign $campaign */
        $campaign = $campaignRepository->findOneByFilter($website, $request->getLocale(), $filter);

        if (!$campaign) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $form = $this->createForm(FrontType::class, new Email(), ['form_data' => $campaign]);
        $form->handleRequest($request);
        $arguments = [
            'configuration' => $configuration,
            'websiteTemplate' => $template,
            'website' => $website,
            'campaign' => $campaign,
            'form' => $form->createView()
        ];

        if ($form->isSubmitted()) {
            $success = $form->isValid() ? $manager->execute($form, $campaign, $form->getData()) : false;
            return new JsonResponse(['success' => $success, 'showModal' => $campaign->getThanksModal(), 'html' => $this->renderView('core/render.html.twig', [
                'html' => $this->subscriber->get(CacheService::class)->parse($this->renderView('front/' . $template . '/actions/newsletter/view.html.twig', $arguments))
            ])]);
        }

        return $this->cache('front/' . $template . '/actions/newsletter/view.html.twig', $campaign, $arguments);
    }
}