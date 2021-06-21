<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Listing;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Module\Newscast\Teaser;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Repository\Module\Newscast\ListingRepository;
use App\Repository\Module\Newscast\NewscastRepository;
use App\Repository\Module\Newscast\TeaserRepository;
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
 * NewscastController
 *
 * Front Newscast renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastController extends FrontController
{
    /**
     * Index
     *
     * @Route("/action/newscast/index/{website}/{url}/{filter}", name="front_newscast_index", methods={"GET"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param NewscastRepository $newscastRepository
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
        NewscastRepository $newscastRepository,
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
            ? $newscastRepository->findMaxResultPublishedListingOrderByNewest($request->getLocale(), $website, $listing, 1) : NULL;
        $entities = $newscastRepository->findByListing($request->getLocale(), $website, $listing, $lastNews);

        $count = count($entities);
        $limit = $listing->getItemsPerPage() ? $listing->getItemsPerPage() : 9;
        $pagination = $this->getPagination($entities, $limit);
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $listing;
        $entity->setUpdatedAt($listing->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/newscast/index.html.twig', $entity, [
            'website' => $website,
            'url' => $url,
            'filter' => $filter,
            'listing' => $listing,
            'scrollInfinite' => $listing->getScrollInfinite(),
            'maxPage' => $count > 0 ? intval(ceil($count / $limit)) : $count,
            'lastNews' => $lastNews,
            'websiteTemplate' => $template,
            'thumbConfigurationFirst' => $this->thumbConfigurationByFilter($website, Newscast::class, 'first-news-index'),
            'thumbConfiguration' => $this->thumbConfiguration($website, Newscast::class, 'index'),
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
        $template = $configuration->getTemplate();
        $locale = $request->getLocale();
        $entities = $listingService->findTeaserEntities($teaser, $locale, Newscast::class, $website);

        $entity = $block instanceof Block ? $block : $teaser;
        $entity->setUpdatedAt($teaser->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/newscast/teaser.html.twig', $entity, [
            'websiteTemplate' => $template,
            'block' => $block,
            'url' => $url,
            'website' => $website,
            'urlsIndex' => $listingService->indexesPages($teaser, $locale, Listing::class, Newscast::class, $entities, []),
            'teaser' => $teaser,
            'entities' => $entities,
            'thumbConfiguration' => $this->thumbConfiguration($website, Newscast::class, 'teaser'),
        ], $configuration->getFullCache());
    }

    /**
     * View
     *
     * @Route({
     *     "fr": "/{pageUrl}/fiche-actualite/{url}",
     *     "en": "/{pageUrl}/news-card/{url}"
     * }, name="front_newscast_view", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Route({
     *     "fr": "/fiche-actualite/{url}",
     *     "en": "/news-card/{url}"
     * }, name="front_newscast_view_only", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param NewscastRepository $newscastRepository
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
        NewscastRepository $newscastRepository,
        PageRepository $pageRepository,
        SeoService $seoService,
        string $url,
        string $pageUrl = NULL,
        bool $preview = false)
    {
        $website = $this->getWebsite($request);

        /** @var Page $page */
        $page = $pageUrl ? $pageRepository->findByUrlCodeAndLocale($website, $pageUrl, $request->getLocale(), $preview) : NULL;
        $news = $newscastRepository->findByUrlAndLocale($url, $website, $request->getLocale(), $preview);

        if (!$news) {
            throw $this->createNotFoundException();
        }

        $category = $news->getCategory();

        /** Add default category if is NULL */
        if (!$category) {
            /** @var Category $defaultCategory */
            $defaultCategory = $this->getDefaultCategory($website);
            $news->setCategory($defaultCategory);
        }

        $url = $news->getUrls()[0];
        $request->setLocale($url->getLocale());

        $cache = $this->masterCache($website, $news);
        if ($cache) {
            return $cache;
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $thumbConfigurationHeader = $this->thumbConfiguration($website, Newscast::class, 'view', NULL, 'titleheader');
        $thumbConfigurationHeader = $thumbConfigurationHeader ? $thumbConfigurationHeader : $this->thumbConfiguration($website, Block::class, NULL, 'titleheader');

        $layout = $category->getLayout() && $category->getLayout()->getZones()->count() > 0 ? $category->getLayout() : NULL;
        $templateCategory = $category;
        if(!$layout && $category->getUseDefaultTemplate()) {
            $defaultCategory = $this->getDefaultCategory($website);
            $layout = $defaultCategory && $defaultCategory->getLayout() && $defaultCategory->getLayout()->getZones()->count() > 0 ? $defaultCategory->getLayout() : NULL;
            $templateCategory = $defaultCategory;
        }

        return $this->cache('front/' . $websiteTemplate . '/actions/newscast/view.html.twig', $news, [
            'templateName' => 'new-view',
            'interface' => $this->getInterface(Newscast::class),
            'websiteTemplate' => $websiteTemplate,
            'seo' => $seoService->execute($url, $news),
            'thumbConfiguration' => $this->thumbConfiguration($website, Newscast::class, 'view'),
            'thumbConfigurationHeader' => $thumbConfigurationHeader,
            'page' => $page,
            'url' => $url,
            'news' => $news,
            'templateCategory' => $templateCategory,
            'layout' => $layout
        ]);
    }

    /**
     * Preview
     *
     * @Route("/admin-%security_token%/front/newscast/preview/{website}/{url}", name="front_newscast_preview", methods={"GET", "POST"}, schemes={"%protocol%"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param ListingService $listingService
     * @param NewscastRepository $newscastRepository
     * @param Website $website
     * @param Url $url
     * @return Response
     * @throws NonUniqueResultException
     */
    public function preview(Request $request, ListingService $listingService, NewscastRepository $newscastRepository, Website $website, Url $url)
    {
        $news = $newscastRepository->findByUrlAndLocale($url->getCode(), $website, $url->getLocale(), true);

        if (!$news) {
            throw $this->createNotFoundException();
        }

        $indexUrls = $listingService->indexesPages($news, $url->getLocale(), Listing::class, Newscast::class, [$news]);
        $request->setLocale($url->getLocale());

        return $this->forward(NewscastController::class . '::view', [
            'pageUrl' => !empty($indexUrls[$news->getId()]) ? $indexUrls[$news->getId()] : NULL,
            'url' => $url->getCode(),
            'preview' => true
        ]);
    }

    /**
     * Get default Category
     *
     * @param Website $website
     * @return Category|null
     */
    private function getDefaultCategory(Website $website): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findOneBy([
            'website' => $website,
            'isDefault' => true
        ]);
    }
}