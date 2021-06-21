<?php

namespace App\Twig\Content;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Category;
use App\Entity\Module\Catalog\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * CatalogRuntime
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogRuntime implements RuntimeExtensionInterface
{
    private $entityManager;
    private $request;

    /**
     * CatalogRuntime constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Get Product FeaturesValueProduct group by Feature
     *
     * @param Product $product
     * @return array
     */
    public function productData(Product $product): array
    {
        $byPosition = [];
        $byName = [];
        $bySlug = [];
        $featuresById = [];
        $featuresBySlug = [];
        $featuresByPosition = [];

        foreach ($product->getValues() as $value) {
            if ($value->getFeature()) {

                $feature = $value->getFeature();

                $byName[$feature->getAdminName()][] = $value;
                $bySlug[$feature->getSlug()][] = $value;
                $byPosition[$value->getFeaturePosition()][$value->getPosition()] = $value;
                $featuresById[$feature->getId()] = $feature;
                $featuresBySlug[$feature->getSlug()] = $feature;
                $featuresByPosition[$value->getFeaturePosition()] = $feature;

                ksort($byName);
                ksort($bySlug);
                ksort($byPosition);
                ksort($byPosition[$value->getFeaturePosition()]);
                ksort($featuresById);
                ksort($featuresBySlug);
                ksort($featuresByPosition);
            }
        }

        return [
            'byName' => $byName,
            'bySlug' => $bySlug,
            'byPosition' => $byPosition,
            'featuresById' => $featuresById,
            'featuresByPosition' => $featuresByPosition,
            'featuresBySlug' => $featuresBySlug,
        ];
    }

    /**
     * Get all categories by Website
     *
     * @param Website $website
     * @param bool $withProducts
     * @param bool $checkOnlineStatus
     * @return array
     */
    public function allCatalogCategories(Website $website, bool $withProducts = false, bool $checkOnlineStatus = false): array
    {
        $locale = $this->request->getLocale();
        $allCategories = $this->entityManager->getRepository(Category::class)->findAllByLocale($website, $locale);

        $categoriesByPosition = [];
        $categoriesBySlug = [];
        foreach ($allCategories as $category) {
            $categoriesByPosition[$category->getPosition()] = $category;
            $categoriesBySlug[$category->getSlug()] = $category;
            ksort($categoriesByPosition);
            ksort($categoriesBySlug);
        }

        if (!$withProducts) {
            return $allCategories;
        }

        $products = $this->entityManager->getRepository(Product::class)->findAllByLocale($website, $locale, $checkOnlineStatus);

        $productsByCategories = [];
        foreach ($products as $product) {
            if($checkOnlineStatus) {
                foreach ($product->getCategories() as $category) {
                    $productsByCategories[$category->getSlug()][] = $product;
                }
            }
        }

        return [
            'categoriesByPosition' => $categoriesByPosition,
            'categoriesBySlug' => $categoriesBySlug,
            'productsByCategories' => $productsByCategories
        ];
    }

    /**
     * Check if Product in cart
     *
     * @param int $id
     * @return string
     */
    public function productInCart(int $id): string
    {
        foreach ($this->productsInCart() as $product) {
            if (intval($product['id']) === $id) {
                return 'on';
            }
        }
        return 'off';
    }

    /**
     * Get Products in cart
     *
     * @return array
     */
    public function productsInCart(): array
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
        $cookiesRequest = $this->request->cookies->get('cart_list');
        $cookies = !empty($cookiesRequest) ? $serializer->decode($cookiesRequest, 'json') : [];
        $ids = [];
        foreach ($cookies as $key => $cookie) {
            if (!$cookie || in_array($cookie['id'], $ids)) {
                unset($cookies[$key]);
            } else {
                $ids[] = $cookie['id'];
            }
        }
        return $cookies;
    }
}