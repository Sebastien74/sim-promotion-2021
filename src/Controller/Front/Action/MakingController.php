<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Making\Category;
use App\Entity\Module\Making\Listing;
use App\Entity\Module\Making\Making;
use App\Entity\Module\Making\Teaser;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Repository\Module\Making\ListingRepository;
use App\Repository\Module\Making\MakingRepository;
use App\Repository\Module\Making\TeaserRepository;
use App\Repository\Layout\PageRepository;
use App\Service\Content\ListingService;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MakingController
 *
 * Front Makings renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MakingController extends FrontController
{
    /**
     * Index
     *
     * @Route("/action/making/index/{website}/{url}/{filter}", name="front_making_index", methods={"GET"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param MakingRepository $makingRepository
     * @param ListingRepository $listingRepository
     * @param Website $website
     * @param Url $url
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(
        Request $request,
        MakingRepository $makingRepository,
        ListingRepository $listingRepository,
        Website $website,
        Url $url,
        Block $block = NULL,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Listing $listing */
        $listing = $listingRepository->find($filter);

        if (!$listing) {
            return new Response();
        }

        $lastNews = $listing->getLargeFirst()
            ? $makingRepository->findMaxResultPublishedListingOrderByNewest($request->getLocale(), $website, $listing, 1) : NULL;
        $entities = $makingRepository->findByListing($request->getLocale(), $website, $listing, $lastNews);

        $count = count($entities);
        $limit = $listing->getItemsPerPage() ? $listing->getItemsPerPage() : 9;
        $pagination = $this->getPagination($entities, $limit);
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();

        $entity = $block instanceof Block ? $block : $listing;
        $entity->setUpdatedAt($listing->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/making/listing.html.twig', $entity, [
            'website' => $website,
            'url' => $url,
            'filter' => $filter,
            'listing' => $listing,
            'scrollInfinite' => $listing->getScrollInfinite(),
            'maxPage' => $count > 0 ? intval(ceil($count / $limit)) : $count,
            'lastNews' => $lastNews,
            'websiteTemplate' => $template,
            'thumbConfiguration' => $this->thumbConfiguration($website, Making::class, 'index'),
            'entities' => $pagination,
            'allEntities' => $lastNews ? array_merge([$lastNews], $entities) : $entities
        ], $configuration->getFullCache());
    }

    /**
     * Teaser
     *
     * @param Request $request
     * @param TeaserRepository $teaserRepository
     * @param ListingService $listingService
     * @param Website $website
     * @param Block $block
     * @param Url $url
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function teaser(
        Request $request,
        TeaserRepository $teaserRepository,
        ListingService $listingService,
        Website $website,
        Block $block,
        Url $url,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Teaser $teaser */
        $teaser = $teaserRepository->findOneByFilter($filter);

        if (!$teaser) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $website->getConfiguration()->getTemplate();
        $locale = $request->getLocale();
        $entities = $listingService->findTeaserEntities($teaser, $locale, Making::class, $website);

        $entity = $block instanceof Block ? $block : $teaser;
        $entity->setUpdatedAt($teaser->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/making/teaser.html.twig', $entity, [
            'websiteTemplate' => $template,
            'block' => $block,
            'url' => $url,
            'website' => $website,
            'urlsIndex' => $listingService->indexesPages($teaser, $locale, Listing::class, Making::class, $entities, []),
            'teaser' => $teaser,
            'entities' => $entities,
            'thumbConfiguration' => $this->thumbConfiguration($website, Making::class, 'teaser'),
        ], $configuration->getFullCache());
    }

    /**
     * View
     *
     * @Route({
     *     "fr": "/{pageUrl}/fiche-realistaion/{url}",
     *     "en": "/{pageUrl}/making-card/{url}"
     * }, name="front_making_view", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param MakingRepository $makingRepository
     * @param PageRepository $pageRepository
     * @param SeoService $seoService
     * @param string $url
     * @param string|null $pageUrl
     * @param bool $preview
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(
        Request $request,
        MakingRepository $makingRepository,
        PageRepository $pageRepository,
        SeoService $seoService,
        string $url,
        string $pageUrl = NULL,
        bool $preview = false)
    {
        $website = $this->getWebsite($request);

        /** @var Page $page */
        $page = $pageUrl ? $pageRepository->findByUrlCodeAndLocale($website, $pageUrl, $request->getLocale(), $preview) : NULL;
        $making = $makingRepository->findByUrlAndLocale($url, $website, $request->getLocale(), $preview);

        if (!$making) {
            throw $this->createNotFoundException();
        }

        // Add default category if is NULL
        if (!$making->getCategory()) {
            /** @var Category $defaultCategory */
            $defaultCategory = $this->entityManager->getRepository(Category::class)->findOneBy([
                'website' => $website,
                'isDefault' => true
            ]);
            $making->setCategory($defaultCategory);
        }

        $request->setLocale($making->getUrls()[0]->getLocale());

        $cache = $this->masterCache($website, $making);
        if ($cache) {
            return $cache;
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();

        return $this->cache('front/' . $websiteTemplate . '/actions/making/view.html.twig', $making, [
            'templateName' => 'new-view',
            'interface' => $this->getInterface(Making::class),
            'websiteTemplate' => $websiteTemplate,
            'seo' => $seoService->execute($making->getUrls()[0], $making),
            'thumbConfiguration' => $this->thumbConfiguration($website, Making::class, 'view'),
            'page' => $page,
            'url' => $making->getUrls()[0],
            'making' => $making
        ]);
    }

    /**
     * Preview
     *
     * @Route("/admin-%security_token%/front/making/preview/{website}/{url}", name="front_making_preview", methods={"GET", "POST"}, schemes={"%protocol%"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param ListingService $listingService
     * @param MakingRepository $makingRepository
     * @param Website $website
     * @param Url $url
     * @return Response
     * @throws NonUniqueResultException
     */
    public function preview(Request $request, ListingService $listingService, MakingRepository $makingRepository, Website $website, Url $url)
    {
//        $news = $newscastRepository->findByUrlAndLocale($url->getCode(), $website, $url->getLocale(), true);
//
//        if(!$news) { throw $this->createNotFoundException(); }
//
//        $indexUrls = $listingService->indexesPages($news, $url->getLocale(), Listing::class, Newscast::class, [$news]);
//        $request->setLocale($url->getLocale());
//
//        return $this->forward(NewscastController::class . '::view', [
//            'pageUrl' => !empty($indexUrls[$news->getId()]) ? $indexUrls[$news->getId()] : NULL,
//            'url' => $url->getCode(),
//            'preview' => true
//        ]);
    }
}