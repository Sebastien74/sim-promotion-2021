<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Catalog\Teaser;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Entity\Module\Catalog\Listing;
use App\Entity\Module\Catalog\Product;
use App\Form\Type\Module\Catalog\FrontSearchFiltersType;
use App\Form\Type\Module\Catalog\FrontSearchTextType;
use App\Repository\Module\Catalog\CategoryRepository;
use App\Repository\Module\Catalog\ListingRepository;
use App\Repository\Module\Catalog\ProductRepository;
use App\Repository\Module\Catalog\TeaserRepository;
use App\Service\Content\CatalogSearchService;
use App\Service\Content\ListingService;
use App\Service\Content\SeoService;
use App\Repository\Layout\PageRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CatalogController
 *
 * Catalog render
 *
 * @property int LIMIT
 * @property array $arguments
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogController extends FrontController
{
    private const LIMIT = 12;
    private $arguments = [];

    /**
     * Index
     *
     * @Route("/module/catalog/index/{website}/{url}/{filter}", methods={"GET"}, name="front_cataloglisting_index", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param ListingRepository $listingRepository
     * @param CatalogSearchService $searchService
     * @param Website $website
     * @param Url $url
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(Request $request, ListingRepository $listingRepository, CatalogSearchService $searchService, Website $website, Url $url, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Listing $listing */
        $listing = $listingRepository->findOneByFilter($filter);
        $this->getArguments($listing, $url);

        if (!$listing) {
            return new Response();
        }

        $this->arguments['websiteTemplate'] = $website->getConfiguration()->getTemplate();
        $this->arguments['filter'] = $filter;
        $this->arguments['template'] = 'front/' . $this->arguments['websiteTemplate'] . '/actions/catalog/index.html.twig';

        return $this->getResults($request, $searchService, $website, $listing, $this->getData(), self::LIMIT);
    }

    /**
     * Search
     *
     * @Route("/module/catalog/search/{listing}/{url}", methods={"GET"}, name="front_catalog_search", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param CatalogSearchService $searchService
     * @param Listing $listing
     * @param Url $url
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function search(Request $request, CatalogSearchService $searchService, Listing $listing, Url $url)
    {
        $data = $this->getData();
        $website = $listing->getWebsite();
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $allProducts = $searchService->execute($listing, $data);

        /** Form text */
        $formText = $this->createForm(FrontSearchTextType::class, NULL, [
            'action' => $this->generateUrl('front_catalog_search', ['listing' => $listing->getId(), 'url' => $url->getId()]),
            'text' => $data['text'],
            'method' => 'GET'
        ]);

        /** Form select */
        $formFilters = $this->createForm(FrontSearchFiltersType::class, $listing, [
            'action' => $this->generateUrl('front_catalog_search', ['listing' => $listing->getId(), 'url' => $url->getId()]),
            'filters' => $data['filters'],
            'products' => $allProducts,
            'website' => $website,
            'method' => 'GET'
        ]);

        $this->getArguments($listing, $url, $allProducts['initialResults']);
        $this->arguments['listing'] = $listing;
        $this->arguments['categories'] = $allProducts['categories'];
        $this->arguments['initialProducts'] = $allProducts['initialResults'];
        $this->arguments['formText'] = $formText->createView();
        $this->arguments['formFilters'] = $formFilters->createView();
        $this->arguments['count'] = count($allProducts['initialResults']);

        if (!empty($request->get('ajax'))) {
            $this->arguments['template'] = 'front/' . $websiteTemplate . '/actions/catalog/results.html.twig';
            $this->getArguments($listing, $url);
            return $this->getResults($request, $searchService, $website, $listing, $data);
        }

        return $this->cache('front/' . $websiteTemplate . '/actions/catalog/form/search.html.twig', NULL, $this->arguments);
    }

    /**
     * Get results
     *
     * @param Request $request
     * @param CatalogSearchService $searchService
     * @param Website $website
     * @param Listing $listing
     * @param array $data
     * @param int $limit
     * @return JsonResponse|Response
     * @throws Exception
     */
    private function getResults(
        Request $request,
        CatalogSearchService $searchService,
        Website $website,
        Listing $listing,
        array $data,
        int $limit = 0)
    {
        $this->arguments['locale'] = $request->getLocale();
        $this->arguments['limit'] = $limit;

        if (!empty($data['text']) && $request->get('ajax')) {
            return new JsonResponse(['html' => $this->executeCommand('cms:catalog:search:text', $data['text'])]);
        } elseif (!empty($data['text'])) {
            $products = $this->getDoctrine()->getRepository(Product::class)->findLikeInTitle($website, $request->getLocale(), $data['text']);
        } elseif (!empty($data['filters'])) {
            $products = $searchService->execute($listing, $data['filters'])['searchResults'];
        } else {
            $products = $searchService->execute($listing)['initialResults'];
        }

        $this->arguments['count'] = $count = count($products);
        $this->arguments['products'] = $limit > 0 ? $this->getPagination($products, $limit) : $products;
        $this->arguments['maxPage'] = $count > 0 && $limit > 0 ? intval(ceil($count / $limit)) : $count;

        if ($request->get('ajax')) {
            return new JsonResponse(['count' => $this->arguments['count'], 'html' => $this->renderView($this->arguments['template'], $this->arguments)]);
        } else {
            return $this->cache($this->arguments['template'], NULL, $this->arguments);
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    private function getData(): array
    {
        $getRequest = filter_input_array(INPUT_GET);
        $text = NULL;
        $filters = NULL;

        if (isset($getRequest['text']) || isset($getRequest['search_products']['text'])) {
            $text = isset($getRequest['search_products']['text']) ? $getRequest['search_products']['text'] : $getRequest['text'];
        } elseif (isset($getRequest['categories']) || isset($getRequest['filters_products'])) {
            $filters = isset($getRequest['filters_products']['categories']) ? $getRequest['filters_products'] : $getRequest;
        }

        return [
            'text' => $text,
            'filters' => $filters
        ];
    }

    /**
     * Get arguments
     *
     * @param Listing $listing
     * @param Url $url
     * @param array $products
     */
    private function getArguments(Listing $listing, Url $url, $products = []): void
    {
        $website = $listing->getWebsite();

        $this->arguments = array_merge($this->arguments, [
            'websiteTemplate' => $website->getConfiguration()->getTemplate(),
            'website' => $website,
            'listing' => $listing,
            'scrollInfinite' => true,
            'highlight' => true,
            'url' => $url,
            'thumbConfiguration' => $this->thumbConfiguration($website, Product::class, 'index'),
            'products' => $products
        ]);
    }

    /**
     * Execute search command
     *
     * @param string $command
     * @param $data
     * @return string
     * @throws Exception
     */
    private function executeCommand(string $command, $data)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $command,
            'arguments' => $this->arguments,
            'post' => $data,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
        return $output->fetch();
    }

    /**
     * View
     *
     * @Route({
     *     "fr": "/{pageUrl}/fiche-produit/{url}",
     *     "en": "/{pageUrl}/product-card/{url}"
     * }, name="front_catalogproduct_view", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Route({
     *     "fr": "/fiche-produit/{url}",
     *     "en": "/product-card/{url}"
     * }, name="front_catalogproduct_view_only", methods={"GET"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param PageRepository $pageRepository
     * @param SeoService $seoService
     * @param string $url
     * @param string|null $pageUrl
     * @param bool $preview
     * @return Response
     * @throws Exception
     */
    public function view(
        Request $request,
        ProductRepository $productRepository,
        PageRepository $pageRepository,
        SeoService $seoService,
        string $url,
        string $pageUrl = NULL,
        bool $preview = false): Response
    {
        $website = $this->getWebsite($request);

        /** @var Page $page */
        $page = $pageUrl ? $pageRepository->findByUrlCodeAndLocale($website, $pageUrl, $request->getLocale(), $preview) : NULL;
        $product = $productRepository->findByUrlAndLocale($url, $website, $request->getLocale(), $preview);
        if (!$product) {
            throw $this->createNotFoundException();
        }

        $request->setLocale($product->getUrls()[0]->getLocale());

        $cache = $this->masterCache($website, $product);
        if ($cache) {
            return $cache;
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $thumbConfigurationHeader = $this->thumbConfiguration($website, Product::class, 'view', NULL, 'titleheader');
        $thumbConfigurationHeader = $thumbConfigurationHeader ? $thumbConfigurationHeader : $this->thumbConfiguration($website, Block::class, NULL, 'titleheader');

        /** Set associated products */
        $associatedProducts = [];
        foreach ($product->getProducts() as $associatedProduct) {
            $associatedProducts[] = $associatedProduct;
        }
        $productsByCategories = $productRepository->findOnlineByCategories($website, $request->getLocale(), $product->getCategories());
        shuffle($productsByCategories);
        $associatedProducts = array_merge($associatedProducts, $productsByCategories);

        return $this->cache('front/' . $websiteTemplate . '/actions/catalog/view.html.twig', $product, [
            'websiteTemplate' => $websiteTemplate,
            'interface' => $this->getInterface(Product::class),
            'seo' => $seoService->execute($product->getUrls()[0], $product),
            'thumbConfiguration' => $this->thumbConfiguration($website, Product::class, 'view'),
            'thumbConfigurationHeader' => $thumbConfigurationHeader,
            'associatedProducts' => $associatedProducts,
            'page' => $page,
            'url' => $product->getUrls()[0],
            'product' => $product
        ]);
    }

    /**
     * Preview
     *
     * @Route("/admin-%security_token%/front/product/preview/{website}/{url}", name="front_catalogproduct_preview", methods={"GET", "POST"}, schemes={"%protocol%"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param ListingService $listingService
     * @param ProductRepository $productRepository
     * @param Website $website
     * @param Url $url
     * @return Response
     * @throws NonUniqueResultException
     */
    public function preview(Request $request, ListingService $listingService, ProductRepository $productRepository, Website $website, Url $url): Response
    {
        $product = $productRepository->findByUrlAndLocale($url->getCode(), $website, $url->getLocale(), true);
        $indexUrls = $listingService->indexesPages($product, $url->getLocale(), Listing::class, Product::class, [$product]);
        $request->setLocale($url->getLocale());

        return $this->forward(CatalogController::class . '::view', [
            'pageUrl' => !empty($indexUrls[$product->getId()]) ? $indexUrls[$product->getId()] : NULL,
            'url' => $url->getCode(),
            'preview' => true
        ]);
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
     * @return JsonResponse|Response
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
        $teaser = $teaserRepository->findOneByFilter($website, $filter);

        if (!$teaser) {
            return new Response();
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $locale = $request->getLocale();
        $entities = $listingService->findTeaserEntities($teaser, $locale, Product::class);

        return $this->cache('front/' . $websiteTemplate . '/actions/catalog/teaser.html.twig', $teaser, [
            'websiteTemplate' => $websiteTemplate,
            'block' => $block,
            'url' => $url,
            'website' => $website,
            'urlsIndex' => $listingService->indexesPages($teaser, $locale, Listing::class, Product::class, $entities),
            'teaser' => $teaser,
            'entities' => $entities,
            'thumbConfiguration' => $this->thumbConfiguration($website, Product::class, 'teaser', $teaser->getSlug()),
        ]);
    }

    /**
     * Teaser categories
     *
     * @param CategoryRepository $categoryRepository
     * @param Website $website
     * @param Block $block
     * @param Url $url
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function teaserCategories(
        CategoryRepository $categoryRepository,
        Website $website,
        Block $block,
        Url $url)
    {
        $categories = $categoryRepository->findBy(['website' => $website]);

        if (!$categories) {
            return new Response();
        }

        $websiteTemplate = $website->getConfiguration()->getTemplate();

        return $this->cache('front/' . $websiteTemplate . '/actions/catalog/teaser-categories.html.twig', NULL, [
            'websiteTemplate' => $websiteTemplate,
            'block' => $block,
            'url' => $url,
            'website' => $website,
            'entities' => $categories,
            'thumbConfiguration' => $this->thumbConfiguration($website, Product::class, 'teaser')
        ]);
    }
}