<?php

namespace App\Service\Content;

use App\Entity\Module\Newscast\Teaser;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Helper\Core\InterfaceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ListingService
 *
 * Manage Listing entities
 *
 * @property InterfaceHelper $interfaceHelper
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property int $entityListingCount
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingService
{
    private $interfaceHelper;
    private $entityManager;
    private $request;
    private $entityListingCount = 0;

    /**
     * ListingService constructor.
     *
     * @param InterfaceHelper $interfaceHelper
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(InterfaceHelper $interfaceHelper, EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->interfaceHelper = $interfaceHelper;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Get indexes pages by Teaser
     *
     * @param mixed $entity
     * @param string $locale
     * @param string $listingClassname
     * @param string $classname
     * @param array $entities
     * @param array $interface
     * @param bool $all
     * @return array
     */
    public function indexesPages($entity, string $locale, string $listingClassname, string $classname, array $entities = [], array $interface = [], bool $all = false): array
    {
        /** @var Website $currentWebsite */
        $currentWebsite = method_exists($entity, 'getWebsite') && $entity->getWebsite() instanceof Website
            ? $entity->getWebsite() : $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
        $interface = $interface ?: $this->interfaceHelper->generate($classname);
        $result = [];
        $entities = $this->parseEntities($entities);

        if (!empty($interface['indexPage']) && $entity) {

            $listings = $this->entityManager->getRepository($listingClassname)->findBy(['website' => $currentWebsite]);
            $getters = $this->getGetters($interface);
            $listingPages = [];
            $listingEntities = [];

            foreach ($listings as $listing) {

                /** @var Website $website */
                $website = $listing->getWebsite();
                $website = $entity instanceof Teaser ? $currentWebsite : $website;
                $listingPages[$listing->getId()] = $this->getPageByAction($website, $locale, $listingClassname, $interface, $listing->getId());

                $propertyGetter = !empty($getters['properties']) ? $getters['properties'] : NULL;
                $entityGetter = !empty($getters['entities']) ? $getters['entities'] : NULL;
                $finder = $propertyGetter && method_exists($listing, $propertyGetter) && $entityGetter && $website && $website->getId() === $currentWebsite->getId();
                if (!$entities && $finder || $all && $finder) {
                    $findEntities = $this->getEntities($classname, $listing, $propertyGetter, $entityGetter);
                    $entities = $all ? array_merge($entities, $findEntities) : $findEntities;
                }

                foreach ($entities as $entity) {
                    if ($this->inListing($listing, $entity, $classname)) {
                        $listingEntities[$listing->getId()][$entity->getId()] = $entity;
                    }
                }
            }

            foreach ($entities as $entity) {
                $this->entityListingCount = 0;
                $result[$entity->getId()] = $this->getUrlCode($listingEntities, $listingPages, $entity, $locale);
            }
        }

        return $result;
    }

    /**
     * To parse entities
     *
     * @param array $entities
     * @return array
     */
    private function parseEntities(array $entities): array
    {
        $result = [];

        foreach ($entities as $entity) {

            if (is_array($entity)) {
                foreach ($entity as $subEntity) {
                    $result[] = $subEntity;
                }
            } else {
                $result[] = $entity;
            }
        }

        return $result;
    }

    /**
     * To parse entities
     *
     * @param $listing
     * @param mixed $entity
     * @param string $classname
     * @return bool|null
     */
    private function inListing($listing, $entity, string $classname): ?bool
    {
        $matches = explode('\\', $classname);
        $isCategory = end($matches) === 'Category';

        if (method_exists($listing, 'getCategories') && $listing->getCategories()->count() === 0) {
            return true;
        } elseif (method_exists($listing, 'getCategories') && method_exists($entity, 'getCategory') && is_object($entity->getCategory())) {
            foreach ($listing->getCategories() as $category) {
                if ($category->getId() === $entity->getCategory()->getId()) {
                    return true;
                }
            }
        } elseif (method_exists($listing, 'getCategories') && method_exists($entity, 'getCategories')) {
            $listingCategoriesIds = [];
            foreach ($listing->getCategories() as $category) {
                $listingCategoriesIds[] = $category->getId();
            }
            foreach ($entity->getCategories() as $category) {
                if (in_array($category->getId(), $listingCategoriesIds)) {
                    return true;
                }
            }
        } elseif ($isCategory) {
            foreach ($listing->getCategories() as $category) {
                if ($category->getId() === $entity->getId()) {
                    return true;
                }
            }
        }

        return NULL;
    }

    /**
     * Get Teaser entities
     *
     * @param $teaser
     * @param string $locale
     * @param string $classname
     * @param Website|null $website
     * @return array
     */
    public function findTeaserEntities($teaser, string $locale, string $classname, Website $website = NULL): array
    {
        /** @var Website $website */
        $website = $website ? $website : $teaser->getWebsite();
        $queryParams = $this->getQueryParams($teaser, $classname);
        $haveCategories = method_exists($teaser, 'getCategories') && $teaser->getCategories()->count() > 0;
        $returnAll = !$haveCategories && $queryParams['getters']['property'] === 'category';
        $cardEntity = !empty($queryParams['interface']['classname']) ? new $queryParams['interface']['classname']() : NULL;
        $cardCategoryProperty = is_object($cardEntity) && method_exists($cardEntity, 'getCategories') ? 'categories' : 'category';
        $referEntity = new $classname();

        $qb = $this->optimizedQueryBuilder($queryParams['getters']['property'], $classname, $locale, $website, $queryParams['sort'], $queryParams['order'])
            ->setMaxResults($queryParams['limit'])
            ->leftJoin('e.' . $queryParams['getters']['property'], $queryParams['getters']['property'])
            ->addSelect($queryParams['getters']['property']);

        if ($teaser->getPromote()) {
            $qb->andWhere('e.promote = :promote')
                ->setParameter('promote', true);
        }

        if ($haveCategories) {
            $categoryIds = [];
            foreach ($teaser->getCategories() as $category) {
                $categoryIds[] = $category->getId();
            }
            if ($categoryIds && $cardCategoryProperty === 'category') {
                $qb->andWhere('e.category IN (:categoryIds)')
                    ->setParameter('categoryIds', $categoryIds);
            } elseif ($categoryIds && $cardCategoryProperty === 'categories' && method_exists($referEntity, 'getCategories')) {
                $qb->leftJoin('e.categories', 'cat')
                    ->andWhere('cat.id IN (:categoryIds)')
                    ->setParameter('categoryIds', $categoryIds);
            } elseif ($categoryIds && $cardCategoryProperty === 'categories') {
                $qb->andWhere('categories.id IN (:categoryIds)')
                    ->setParameter('categoryIds', $categoryIds);
            }
        }

        $entities = $qb->getQuery()->getResult();

        $mappingIds = [];
        $getter = $queryParams['getters']['properties'];

        if (method_exists($teaser, $getter)) {
            foreach ($teaser->$getter() as $property) {
                $mappingIds[] = $property->getId();
            }
        }

        if ($returnAll) {
            $result = $entities;
        } else {

            $result = [];
            $inResult = [];
            $getter = $queryParams['getters']['singleProperty'];

            foreach ($entities as $entity) {
                if ($entity->$getter() instanceof PersistentCollection) {
                    if ($cardCategoryProperty === 'category') {
                        foreach ($entity->$getter() as $property) {
                            if (in_array($property->getPosition(), $mappingIds) || !$mappingIds && !in_array($entity->getId(), $inResult)) {
                                $result[$property->getPosition()][] = $entity;
                                $inResult[] = $entity->getId();
                            }
                        }
                    } elseif ($cardCategoryProperty === 'categories') {
                        foreach ($entity->getCategories() as $category) {
                            if (in_array($category->getId(), $mappingIds) || !$mappingIds && !in_array($entity->getId(), $inResult)) {
                                $result[$category->getPosition()][] = $entity;
                                $inResult[] = $entity->getId();
                            }
                        }
                    }
                } elseif ($entity->$getter() && in_array($entity->$getter()->getId(), $mappingIds) || !$mappingIds) {
                    if (method_exists($entity->$getter(), 'getPosition')) {
                        $result[$entity->$getter()->getPosition()][] = $entity;
                    }
                }
            }
        }

        return $this->sortResult($queryParams, $result, $returnAll);
    }

    /**
     * Get Query params
     *
     * @param $teaser
     * @param string $classname
     * @return array
     */
    private function getQueryParams($teaser, string $classname): array
    {
        $params['limit'] = $teaser->getNbrItems() ? $teaser->getNbrItems() : 5;
        $params['orderBy'] = explode('-', $teaser->getOrderBy());
        $params['sort'] = !empty($params['orderBy'][0]) ? $params['orderBy'][0] : 'publicationStart';
        $params['order'] = !empty($params['orderBy'][1]) ? strtoupper($params['orderBy'][1]) : 'DESC';
        $params['interface'] = $this->interfaceHelper->generate($classname);
        $params['sortByMapping'] = $params['sort'] == $params['interface']['indexPage'];
        $params['sortMapping'] = $params['sortByMapping'] ? $params['order'] : NULL;
        $params['sortMapping'] = $params['sortByMapping'] ? 'DESC' : $params['sortMapping'];
        $params['getters'] = $this->getGetters($params['interface']);

        return $params;
    }

    /**
     * Get getters
     *
     * @param array $interface
     * @return array
     */
    private function getGetters(array $interface): array
    {
        $mappingProperty = substr($interface['indexPage'], -1) === 'y' ? rtrim($interface['indexPage'], "y") . 'ies' : $interface['indexPage'] . 's';
        $mappingProperty = substr($interface['indexPage'], -1) === 's' ? $interface['indexPage'] : $mappingProperty;
        $mappingEntity = substr($interface['name'], -1) === 'y' ? rtrim($interface['name'], "y") . 'ies' : $interface['name'] . 's';

        return [
            'property' => $interface['indexPage'],
            'singleProperty' => 'get' . ucfirst($interface['indexPage']),
            'properties' => 'get' . ucfirst($mappingProperty),
            'entity' => 'get' . ucfirst($interface['name']),
            'entities' => 'get' . ucfirst($mappingEntity)
        ];
    }

    /**
     * Get entities
     *
     * @param string $classname
     * @param mixed $parent
     * @param string $propertyGetter
     * @param string $entityGetter
     * @return array
     */
    private function getEntities(string $classname, $parent, string $propertyGetter, string $entityGetter): array
    {
        $entities = [];
        $propertiesCount = 0;

        if (method_exists($parent, $propertyGetter) && $parent->$propertyGetter()->count() > 0) {
            foreach ($parent->$propertyGetter() as $property) {
                if (method_exists($property, $entityGetter)) {
                    foreach ($property->$entityGetter() as $entity) {
                        $entities[$entity->getId()] = $entity;
                    }
                }
                $propertiesCount++;
            }
        } else {
            $entitiesDb = $this->entityManager->getRepository($classname)->findBy(['website' => $parent->getWebsite()]);
            foreach ($entitiesDb as $entity) {
                $entities[$entity->getId()] = $entity;
            }
        }

        ksort($entities);

        return $entities;
    }

    /**
     * Get locale Page Url code
     *
     * @param array $listingEntities
     * @param array $listingPages
     * @param mixed $entity
     * @param string $locale
     * @return string|null
     */
    private function getUrlCode(array $listingEntities, array $listingPages, $entity, string $locale): ?string
    {
        if (is_object($entity) && method_exists($entity, 'getUrls')) {
            foreach ($entity->getUrls() as $url) {
                /** @var Url $url */
                if ($url->getLocale() === $locale && $url->getIndexPage() && $url->getIsOnline()) {
                    foreach ($url->getIndexPage()->getUrls() as $pageUrl) {
                        if ($pageUrl->getLocale() === $locale) {
                            return $pageUrl->getCode();
                        }
                    }
                }
            }
        }

        foreach ($listingEntities as $listingId => $listingProperties) {
            if (!empty($listingEntities[$listingId][$entity->getId()]) && count($listingProperties) > $this->entityListingCount) {
                $this->entityListingCount = count($listingProperties);
                return $listingPages[$listingId];
            }
        }

        return NULL;
    }

    /**
     * PublishedQueryBuilder
     *
     * @param string $mappingProperty
     * @param string $classname
     * @param string $locale
     * @param Website $website
     * @param string|null $sort
     * @param string|null $order
     * @param bool $preview
     * @return QueryBuilder
     */
    private function optimizedQueryBuilder(
		string $mappingProperty,
		string $classname,
		string $locale,
		Website $website,
		string $sort = NULL,
		string $order = NULL,
		bool $preview = false): QueryBuilder
    {
        $referEntity = new $classname();
        $sort = $sort ? $sort : 'publicationStart';
        $order = $order ? $order : 'DESC';

        $repository = $this->entityManager->getRepository($classname);
        $statement = $repository->createQueryBuilder('e')
            ->leftJoin('e.website', 'w')
            ->leftJoin('e.urls', 'u')
            ->leftJoin('u.seo', 's')
            ->leftJoin('e.' . $mappingProperty, 'c')
            ->andWhere('e.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('website', $website)
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('u')
            ->addSelect('s')
            ->addSelect('c');

        if ($sort !== 'random') {
            $statement->orderBy('e.' . $sort, $order);
        }

        if (method_exists($referEntity, 'getPublicationStart')) {
            $statement->andWhere('e.publicationStart IS NULL OR e.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('e.publicationStart IS NOT NULL');
        }

        if (method_exists($referEntity, 'getPublicationEnd')) {
            $statement->andWhere('e.publicationEnd IS NULL OR e.publicationEnd > CURRENT_TIMESTAMP()');
        }

        if (!$preview) {
            $statement->andWhere('u.isOnline = :isOnline')
                ->setParameter('isOnline', true);
        }

        return $statement;
    }

    /**
     * To sort result
     *
     * @param array $queryParams
     * @param array $result
     * @param bool $returnAll
     * @return array
     */
    private function sortResult(array $queryParams = [], array $result = [], bool $returnAll = false): array
    {
        $response = [];
        $sortDates = $queryParams['sort'] && preg_match('/publication/', $queryParams['sort']);
        $sortCategories = $queryParams['sort'] && preg_match('/category/', $queryParams['sort']);
        $sortPositions = $queryParams['sort'] && preg_match('/position/', $queryParams['sort']);
        $sortRandom = $queryParams['sort'] && preg_match('/random/', $queryParams['sort']);

        if ($sortRandom) {
            foreach ($result as $key => $value) {
                shuffle($result[$key]);
            }
        } else if ($sortPositions) {
            foreach ($result as $key => $value) {
                if (method_exists($value, 'getPosition')) {
                    $response[$value->getPosition()][] = $value;
                }
            }
        } else {
            foreach ($result as $key => $value) {
                if ($sortDates && method_exists($value, 'getPublicationStart') && $value->getPublicationStart() instanceof DateTime) {
                    $response[$value->getPublicationStart()->format('YmdHis')][$value->getPosition()] = $value;
                    ksort($response[$value->getPublicationStart()->format('YmdHis')]);
                } elseif ($sortCategories && method_exists($value, 'getCategory') && $value->getCategory()) {
                    $response[$value->getCategory()->getPosition()][$value->getPosition()] = $value;
                    ksort($response[$value->getCategory()->getPosition()]);
                } elseif (is_iterable($value) && $sortDates) {
                    foreach ($value as $keyValue => $item) {
                        if (method_exists($item, 'getPublicationStart') && $item->getPublicationStart() instanceof DateTime) {
                            $response[$item->getPublicationStart()->format('YmdHis')][$item->getPosition()] = $item;
                            ksort($response[$item->getPublicationStart()->format('YmdHis')]);
                        } elseif ($sortCategories && method_exists($item, 'getCategory') && $item->getCategory()) {
                            $response[$value->getCategory()->getPosition()][$item->getPosition()] = $item;
                            ksort($response[$item->getCategory()->getPosition()]);
                        }
                    }
                }
            }
        }

        if ($queryParams['sortByMapping'] && $queryParams['sortMapping'] === 'ASC'
            || !$queryParams['sortByMapping'] && $queryParams['order'] === 'ASC') {
            ksort($response);
        } elseif ($queryParams['sortByMapping'] && $queryParams['sortMapping'] === 'DESC'
            || !$queryParams['sortByMapping'] && $queryParams['order'] === 'DESC') {
            asort($response, true);
            krsort($result);
        }

        return $response ?: $result;
    }

    /**
     * Get Page by Action
     *
     * @param Website $website
     * @param string $locale
     * @param string $classname
     * @param array $interface
     * @param int|null $entityId
     * @return string|null
     */
    private function getPageByAction(Website $website, string $locale, string $classname, array $interface, int $entityId): ?string
    {
        /** @var Page $page */
        $page = $this->entityManager->getRepository(Page::class)->findByAction(
            $website,
            $locale,
            $classname,
            $entityId,
            $interface['name'] . '-index'
        );

        if ($page) {
            foreach ($page->getUrls() as $pageUrl) {
                if ($pageUrl->getLocale() === $locale && $pageUrl->getIsOnline()) {
                    return $pageUrl->getCode();
                }
            }
        }

        return NULL;
    }
}