<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Category;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\Listing;
use App\Entity\Module\Catalog\ListingFeatureValue;
use App\Entity\Module\Catalog\Product;
use App\Repository\Module\Catalog\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CatalogSearchService
 *
 * @property EntityManagerInterface $entityManager
 * @property ProductRepository $productRepository
 * @property Request $request
 * @property string $locale
 * @property Product[] $products
 * @property Website $website
 * @property Listing $listing
 * @property bool $displayAll
 * @property array $cache
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogSearchService
{
    private $entityManager;
    private $productRepository;
    private $request;
    private $locale;
    private $products = [];
    private $website;
    private $listing;
    private $displayAll = false;
    private $cache = [];

    /**
     * CatalogSearchService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->request = $requestStack->getMasterRequest();
        if ($this->request) {
            $this->locale = $this->request->getLocale();
        }
    }

    /**
     * Execute service
     *
     * @param Listing $listing
     * @param array $data
     * @return array
     */
    public function execute(Listing $listing, array $data = []): array
    {
        $this->website = $listing->getWebsite();
        $this->listing = $listing;
        $this->displayAll = $this->listing->getDisplay() === 'all';

        $catalogs = $this->getByCatalogs();
        $categories = $this->getByCategories($catalogs);
        $this->getByValues();
        $this->setCache($this->products, 'products-values');

        return [
            'initial' => $this->cache,
            'initialResults' => $this->products,
            'searchResults' => $this->getSearch($data),
            'categories' => $categories
        ];
    }

    private function getByCatalogs()
    {
        $catalogs = [];
        if ($this->listing->getCatalogs()->count() > 0 && !$this->displayAll) {
            $catalogs = $this->listing->getCatalogs();
        } elseif ($this->displayAll) {
            $catalogs = $this->entityManager->getRepository(Catalog::class)->findBy(['website' => $this->website]);
        }

        if ($catalogs) {
            $products = $this->productRepository->findOnlineByCatalogs($this->website, $this->locale, $catalogs);
            $this->setProducts($products, true);
        }

        $this->setCache($catalogs, 'catalogs');

        return $catalogs;
    }

    private function getByCategories(array $catalogs)
    {
        $categories = [];
        $categoriesToDisplay = [];
        if (!empty($this->request->get('category'))) {
            /** For links request */
            $categories = $this->entityManager->getRepository(Category::class)->findBy([
                'slug' => $this->request->get('category'),
                'website' => $this->website
            ]);
            $categoriesToDisplay = $this->entityManager->getRepository(Category::class)->findBy(['website' => $this->website]);
        } elseif ($this->listing->getCategories()->count() > 0 && !$this->displayAll) {
            $categories = $categoriesToDisplay = $this->listing->getCategories();
        } elseif ($this->displayAll || !empty($this->listing->getSearchCategories()) && $this->listing->getCategories()->count() === 0) {
            $categories = $categoriesToDisplay = $this->entityManager->getRepository(Category::class)->findBy(['website' => $this->website]);
        }

        if ($categories) {
            $products = $this->productRepository->findOnlineByCategories($this->website, $this->locale, $categories);
            $this->setProducts($products, !$catalogs);
        }

        $this->setCache($categories, 'categories');

        return $categoriesToDisplay;
    }

    private function getByValues()
    {
        $featuresValues = [];
        if ($this->listing->getFeatures()->count() > 0 && !$this->displayAll && $this->listing->getFeaturesValues()->count() === 0) {
            $featuresValues = $this->listing->getFeatures();
        } elseif (!$this->displayAll && $this->listing->getFeaturesValues()->count() > 0) {
            $featuresValues = $this->listing->getFeaturesValues();
        } elseif ($this->displayAll) {
            $featuresValues = $this->entityManager->getRepository(FeatureValue::class)->findBy(['website' => $this->website]);
        }

        $values = $this->setValues($featuresValues);

        if ($values) {
            $products = $this->productRepository->findOnlineByValues($this->website, $this->locale, $values, 'OR');
            $this->setProducts($products);
        }
    }

    private function setValues($values): array
    {
        $result = [];
        foreach ($values as $value) {
            if ($value instanceof Feature) {
                foreach ($value->getValues() as $listingValue) {
                    $result[] = $listingValue;
                }
            } elseif ($value instanceof ListingFeatureValue) {
                $result[] = $value->getValue();
            } else {
                $result[] = $value;
            }
        }

        $this->setCache($result, 'values');

        return $result;
    }

    private function setProducts(array $products, bool $init = false)
    {
        if (!$this->products && $init) {
            foreach ($products as $product) {
                $this->products[$product->getId()] = $product;
            }
        }

        foreach ($this->products as $product) {

            $match = false;
            foreach ($products as $productToParse) {
                if ($product->getId() === $productToParse->getId()) {
                    $match = true;
                }
            }

            if (!$match) {
                unset($this->products[$product->getId()]);
            }
        }
    }

    private function setCache($entities, string $keyName)
    {
        foreach ($entities as $entity) {
            if ($entity instanceof Product) {
                foreach ($entity->getValues() as $value) {
                    $this->cache[$keyName][$value->getValue()->getSlug()][] = $value;
                    ksort($this->cache[$keyName]);
                }
                foreach ($entity->getCategories() as $category) {
                    $this->cache['products-categories'][$category->getSlug()][$entity->getId()] = $entity;
                }
            } elseif ($entity instanceof FeatureValue) {
                $this->cache[$keyName][$entity->getCatalogfeature()->getSlug()][$entity->getSlug()] = $entity;
                ksort($this->cache[$keyName][$entity->getCatalogfeature()->getSlug()]);
            } else {
                $this->cache[$keyName][$entity->getId()] = $entity;
                ksort($this->cache[$keyName]);
            }
        }

        if (empty($this->cache[$keyName])) {
            $this->cache[$keyName] = [];
        }
    }

    private function getSearch(array $data)
    {
        $searchResult = $this->products;

        if ($data) {
            $productIds = $this->getProductIds($data);
            $searchResult = $this->setResult($productIds, $searchResult, $data);
        }

        return $searchResult;
    }

    private function getProductIds(array $data): array
    {
        $categoryProductIds = [];
        $catalogProductIds = [];
        $valueProductIds = [];

        foreach ($data as $key => $value) {
            foreach ($this->products as $product) {
                if ($key === 'categories' && !empty($this->cache['products-categories'][$value])
                    || $key === 'filters' && is_array($value) && !empty($value['categories'])) {
                    $categories = $product->getCategories();
                    foreach ($categories as $category) {
                        $categoryProductIds[$category->getSlug()][$product->getId()] = $product;
                    }
                } elseif ($key === 'catalogs') {
                    $catalogProductIds[$product->getCatalog()->getSlug()][$product->getId()] = $product;
                } elseif ($key !== 'categories' && $key !== 'catalogs') {
                    foreach ($product->getValues() as $value) {
                        $productValue = $value->getValue();
                        $valueProductIds[$productValue->getCatalogfeature()->getSlug()][$productValue->getSlug()][$product->getId()] = $product;
                    }
                }
            }
        }

        return [
            'categories' => $categoryProductIds,
            'catalogs' => $catalogProductIds,
            'values' => $valueProductIds
        ];
    }

    private function setResult(array $productIds, array $searchResult, array $data)
    {
        $categories = !empty($data['categories']) ? $data['categories'] : (!empty($data['filters']['categories']) ? $data['filters']['categories'] : NULL);
        if ($categories) {
            foreach ($this->products as $key => $product) {
                if (empty($productIds['categories'][$categories][$product->getId()])) {
                    unset($searchResult[$key]);
                }
            }
        }

        if (!empty($data['catalogs'])) {
            foreach ($this->products as $key => $product) {
                if (empty($productIds['catalogs'][$data['catalogs']][$product->getId()])) {
                    unset($searchResult[$key]);
                }
            }
        }

        $valuesSearch = [];
        foreach ($data as $name => $value) {
            if ($name !== "categories" && $name !== "catalogs" && !empty($value)) {
                $valuesSearch[$name][] = $value;
            }
        }

        if (!empty($productIds['values']) && !empty($valuesSearch)) {
            foreach ($searchResult as $key => $product) {
                $countValues = 0;
                foreach ($valuesSearch as $featureName => $values) {
                    foreach ($values as $slug) {
                        if (!empty($productIds['values'][$featureName][$slug][$product->getId()])) {
                            $countValues++;
                        }
                    }
                }
                if ($countValues < count($valuesSearch)) {
                    unset($searchResult[$key]);
                }
            }
        }

        return $searchResult;
    }
}