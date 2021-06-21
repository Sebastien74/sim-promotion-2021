<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Search\Search;
use App\Entity\Core\Website;
use App\Entity\Seo\Url;
use App\Form\Manager\Front\SearchManager;
use App\Form\Type\Module\Search\FrontType;
use App\Repository\Module\Search\SearchRepository;
use App\Repository\Layout\PageRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SearchController
 *
 * Front Search renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/search/view/{website}/{filter}", methods={"GET"}, name="front_search_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param SearchRepository $searchRepository
     * @param PageRepository $pageRepository
     * @param Website $website
     * @param null|string|int $filter
     * @return Response
     * @throws Exception
     */
    public function view(
        Request $request,
        SearchRepository $searchRepository,
        PageRepository $pageRepository,
        Website $website,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Search $search */
        $search = $searchRepository->findOneByFilter($website, $filter);

        if (!$search) {
            return new Response();
        }

        $template = $website->getConfiguration()->getTemplate();
        $resultsPageUrl = $pageRepository->findOneUrlByPageAndLocale($request->getLocale(), $search->getResultsPage());
        $urlCode = $resultsPageUrl instanceof Url ? $resultsPageUrl->getCode() : NULL;
        $form = $this->createForm(FrontType::class, NULL, [
            'field_data' => NULL,
            'action' => $this->generateUrl('front_index', ['url' => $urlCode]),
            'method' => 'GET'
        ]);

        return $this->cache('front/' . $template . '/actions/search/view.html.twig', $search, [
            'resultsPage' => $resultsPageUrl,
            'search' => $search,
            'websiteTemplate' => $template,
            'form' => $form->createView(),
            'website' => $website
        ]);
    }

    /**
     * Results view
     *
     * @Route("/front/search/results/{website}/{filter}", methods={"GET"}, name="front_search_results", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param RequestStack $requestStack
     * @param SearchRepository $searchRepository
     * @param PageRepository $pageRepository
     * @param Website $website
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function results(
        Request $request,
        RequestStack $requestStack,
        SearchRepository $searchRepository,
        PageRepository $pageRepository,
        Website $website,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Search $search */
        $search = $searchRepository->findOneByFilter($website, $filter);

        if (!$search) {
            return new Response();
        }

        $searchText = urldecode($requestStack->getMasterRequest()->get('search'));
        $resultsPageUrl = $pageRepository->findOneUrlByPageAndLocale($request->getLocale(), $search->getResultsPage());
        $urlCode = $resultsPageUrl instanceof Url ? $resultsPageUrl->getCode() : NULL;
        $form = $this->createForm(FrontType::class, NULL, [
            'field_data' => $searchText,
            'action' => $this->generateUrl('front_index', ['url' => $urlCode]),
            'method' => 'GET'
        ]);

        $results = $searchText ? $this->subscriber->get(SearchManager::class)->execute($search, $website, $searchText) : (object)[];
        $template = $website->getConfiguration()->getTemplate();
        $currentPage = $requestStack->getMasterRequest()->get('page') ? $requestStack->getMasterRequest()->get('page') : 1;
        $allResults = property_exists($results, 'results') ? $results->results : [];

        return $this->cache('front/' . $template . '/actions/search/results.html.twig', $search, [
            'searchText' => $searchText,
            'resultsPageUrl' => $resultsPageUrl,
            'search' => $search,
            'currentPage' => $currentPage,
            'websiteTemplate' => $template,
            'form' => $form->createView(),
            'website' => $website,
            'results' => !empty($allResults[$currentPage]) ? $allResults[$currentPage] : [],
            'allResults' => $allResults,
            'counts' => property_exists($results, 'counts') ? $results->counts : 0
        ]);
    }
}