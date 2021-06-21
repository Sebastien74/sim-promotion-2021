<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Portfolio\Category;
use App\Entity\Module\Portfolio\Listing;
use App\Entity\Module\Portfolio\Teaser;
use App\Entity\Module\Portfolio\Card;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Repository\Module\Portfolio\CardRepository;
use App\Repository\Module\Portfolio\CategoryRepository;
use App\Repository\Module\Portfolio\ListingRepository;
use App\Repository\Module\Portfolio\TeaserRepository;
use App\Repository\Layout\PageRepository;
use App\Service\Content\ListingService;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PortfolioController
 *
 * Front Portfolio renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PortfolioController extends FrontController
{
    /**
     * Index
     *
     * @param Request $request
     * @param CardRepository $cardRepository
     * @param ListingRepository $listingRepository
     * @param Website $website
     * @param Url $url
     * @param Block|null $block
     * @param null $filter
     * @return Response
     * @throws Exception
     */
    public function index(
        Request $request,
        CardRepository $cardRepository,
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

        $entities = $cardRepository->findByListing($request->getLocale(), $website, $listing);
        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();

        $categories = [];
        foreach ($entities as $entity) {
            foreach ($entity->getCategories() as $category) {
                if(!isset($categories[$category->getPosition()])) {
                    $categories[$category->getPosition()] = $category;
                }
            }
        }

        $entity = $block instanceof Block ? $block : $listing;
        $entity->setUpdatedAt($listing->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/portfolio/index.html.twig', $entity, [
            'website' => $website,
            'url' => $url,
            'filter' => $filter,
            'listing' => $listing,
            'categories' => $categories,
            'entities' => $entities,
            'websiteTemplate' => $template,
            'thumbConfiguration' => $this->thumbConfiguration($website, Card::class, 'index'),
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
     * @param null $filter
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
        $entities = $listingService->findTeaserEntities($teaser, $locale, Card::class, $website);

        $entity = $block instanceof Block ? $block : $teaser;
        $entity->setUpdatedAt($teaser->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/portfolio/teaser.html.twig', $entity, [
            'websiteTemplate' => $template,
            'block' => $block,
            'url' => $url,
            'website' => $website,
            'urlsIndex' => $listingService->indexesPages($teaser, $locale, Listing::class, Card::class, $entities, []),
            'teaser' => $teaser,
            'entities' => $entities,
            'thumbConfiguration' => $this->thumbConfiguration($website, Card::class, 'teaser'),
        ], $configuration->getFullCache());
    }

    /**
     * Category View
     *
     * @Route({
     *     "fr": "/{pageUrl}/portfolio-categorie/{url}",
     *     "en": "/{pageUrl}/portfolio-category/{url}"
     * }, name="front_portfoliocategory_view", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Route({
     *     "fr": "/portfolio-categorie/{url}",
     *     "en": "/portfolio-category/{url}"
     * }, name="front_portfoliocategory_view_only", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param PageRepository $pageRepository
     * @param SeoService $seoService
     * @param string $url
     * @param string|null $pageUrl
     * @param bool $preview
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function category(
        Request $request,
        CategoryRepository $categoryRepository,
        PageRepository $pageRepository,
        SeoService $seoService,
        string $url,
        string $pageUrl = NULL,
        bool $preview = false)
    {
        $website = $this->getWebsite($request);

        /** @var Page $page */
        $page = $pageUrl ? $pageRepository->findByUrlCodeAndLocale($website, $pageUrl, $request->getLocale(), $preview) : NULL;
        $category = $categoryRepository->findByUrlAndLocale($url, $website, $request->getLocale(), $preview);

        if (!$category) {
            throw $this->createNotFoundException();
        }

        $url = $category->getUrls()[0];
        $request->setLocale($url->getLocale());

        $cache = $this->masterCache($website, $category);
        if ($cache) {
            return $cache;
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();

        return $this->cache('front/' . $websiteTemplate . '/actions/portfolio/category.html.twig', $category, [
            'templateName' => 'new-view',
            'interface' => $this->getInterface(Category::class),
            'websiteTemplate' => $websiteTemplate,
            'seo' => $seoService->execute($url, $category),
            'thumbConfiguration' => $this->thumbConfiguration($website, Category::class, 'category'),
            'page' => $page,
            'url' => $url,
            'category' => $category
        ]);
    }

    /**
     * Card View
     *
     * @Route({
     *     "fr": "/{pageUrl}/fiche-portfolio/{url}",
     *     "en": "/{pageUrl}/portfolio-card/{url}"
     * }, name="front_portfoliocard_view", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Route({
     *     "fr": "/fiche-portfolio/{url}",
     *     "en": "/portfolio-card/{url}"
     * }, name="front_portfoliocard_view_only", methods={"GET"}, schemes={"%protocol%"})
     *
     */
    public function view()
    {

    }
}