<?php

namespace App\Form\Manager\Front;

use App\Entity\Module\Search\Search;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Media\Media;
use App\Helper\Core\InterfaceHelper;
use App\Service\Content\SitemapService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SearchManager
 *
 * Manage front Search Action
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property InterfaceHelper $interfaceHelper
 * @property SitemapService $sitemapService
 * @property TranslatorInterface $translator
 * @property string $uploadDirname
 * @property array $sitemap
 * @property array $results
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchManager
{
    private $entityManager;
    private $request;
    private $interfaceHelper;
    private $sitemapService;
    private $translator;
    private $uploadDirname;
    private $sitemap;
    private $results = [];

    private const BOOLEAN_MODE = "IN BOOLEAN MODE";
    private const LANGUAGE_MODE = "IN NATURAL LANGUAGE MODE";

    /**
     * SearchManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param InterfaceHelper $interfaceHelper
     * @param SitemapService $sitemapService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        InterfaceHelper $interfaceHelper,
        SitemapService $sitemapService,
        TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
        $this->interfaceHelper = $interfaceHelper;
        $this->sitemapService = $sitemapService;
        $this->translator = $translator;
    }

    /**
     * Execute request
     *
     * @param Search $search
     * @param Website $website
     * @param string $text
     * @return object
     * @throws NonUniqueResultException
     */
    public function execute(Search $search, Website $website, string $text)
    {
        $results = [];
        $mode = self::BOOLEAN_MODE;
        $textParameter = $search->getSearchType() === 'sentence'
            ? '"' . $text . '"' : $text . '*';

        $this->uploadDirname = 'uploads/' . $website->getUploadDirname() . '/';

        foreach ($search->getEntities() as $classname) {

            $interface = $this->interfaceHelper->generate($classname);

            if ($classname === 'pdf') {
                $pdf = $this->getPDF($mode, $website, $text);
                if ($pdf) {
                    $results['pdf'][] = $pdf;
                }
            } else {
                $queryResult = $this->getByClassname($classname, $mode, $website, $textParameter);
                if ($queryResult) {
                    $results[$interface['name']][] = $queryResult;
                }
            }
        }

        return $this->init($search, $results, $website);
    }

    /**
     * Get PDF
     *
     * @param string $mode
     * @param Website $website
     * @param string $textParameter
     * @return array
     */
    private function getPDF(string $mode, Website $website, string $textParameter)
    {
        $repository = $this->entityManager->getRepository(Media::class);

        $against = "(";
        $against .= "(MATCH_AGAINST(m.filename, :search '" . $mode . "') * 5) + ";
        $against .= "(MATCH_AGAINST(m.name, :search '" . $mode . "') * 4) + ";
        $against = rtrim($against, '+ ') . ") as score";

        return $repository->createQueryBuilder('m')->select('m')
            ->andWhere('m.extension = :extension')
            ->andWhere('m.website = :website')
            ->setParameter('search', strtolower($textParameter . '*'))
            ->setParameter('website', $website)
            ->setParameter('extension', 'pdf')
            ->addSelect($against)
            ->having('score > 0')
            ->orderBy('score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get result by classname
     *
     * @param string $classname
     * @param string $mode
     * @param Website $website
     * @param string $textParameter
     * @return array
     */
    private function getByClassname(string $classname, string $mode, Website $website, string $textParameter)
    {

        $entityObj = new $classname();
        $repository = $this->entityManager->getRepository($classname);

        $against = "(";
        $against .= "(MATCH_AGAINST(i.title, :search '" . $mode . "') * 5) + ";
        $against .= "(MATCH_AGAINST(i.introduction, :search '" . $mode . "') * 4) + ";
        $against .= "(MATCH_AGAINST(i.body, :search '" . $mode . "') * 3) + ";
        $against = rtrim($against, '+ ') . ") as score";

        $statement = $repository->createQueryBuilder('e')->select('e')
            ->andWhere('i.locale = :locale')
            ->andWhere('i.website = :website')
            ->setParameter('locale', $this->request->getLocale())
            ->setParameter('website', $website)
            ->setParameter('search', $textParameter)
            ->addSelect($against)
            ->addSelect('i')
            ->groupBy('e.id')
//            ->having('score > 0')
            ->orderBy('score', 'DESC');

        if (method_exists($entityObj, 'getI18ns')) {
            $statement->leftJoin('e.i18ns', 'i');
        } elseif (method_exists($entityObj, 'getI18n')) {
            $statement->leftJoin('e.i18n', 'i');
        }

        return $statement->getQuery()->getResult();
    }

    /**
     * Init results
     *
     * @param Search $search
     * @param array $queryResults
     * @param Website $website
     * @return object
     * @throws NonUniqueResultException
     */
    private function init(Search $search, array $queryResults, Website $website)
    {
        $results = [];
        $orderBy = $search->getOrderBy();
        $count = 0;
        $page = 1;
        $limit = $search->getItemsPerPage();
        $countLimit = 0;
        $this->sitemap = $this->sitemapService->execute($website, $this->request->getLocale(), true);

        foreach ($queryResults as $interfaceName => $result) {

            if (!empty($result[0])) {

                if (is_array($result[0])) {

                    foreach ($result[0] as $entity) {

                        $isFormField = !empty($entity[0]) && $entity[0] instanceof Block && !empty($entity[0]->getFieldConfiguration());
                        $entity = !$isFormField && $entity[0] instanceof Block ? $this->getByLayout($entity) : $entity;
                        $interfaceName = !empty($entity['interfaceName']) ? $entity['interfaceName'] : $interfaceName;
                        $infos = is_array($entity) ? $this->getInfos($entity[0], $interfaceName) : NULL;

                        if ($infos && !$isFormField) {

                            $orderKey = $this->getOrderKey($orderBy, $infos, $entity);
                            $count++;
                            $results['count'] = $count;
                            $infosRow = ['entity' => $entity[0], 'infos' => $infos['infos'], 'url' => $infos['url']];

                            if ($search->getFilterGroup()) {
                                $existing = $this->setGroups($page, $infos, $orderBy, $orderKey, $infosRow);
                            } else {
                                $existing = $this->setItems($page, $orderKey, $infosRow, $orderBy);
                            }

                            if (!$existing) {
                                $countLimit++;
                            }

                            if ($countLimit === $limit) {
                                $countLimit = 0;
                                $page++;
                            }
                        }
                    }
                }
            }
        }

        return (object)[
            'results' => $this->results,
            'counts' => $this->getCounts($search)
        ];
    }

    private function getByLayout($entity)
    {
        if (!empty($entity[0]) && $entity[0] instanceof Block) {

            $layout = $entity[0]->getCol()->getZone()->getLayout();

            $layoutEntities = [];
            $interfaces = [];
            $allMetasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
            foreach ($allMetasData as $metaData) {
                $classname = $metaData->getName();
                $referEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;
                if ($referEntity && method_exists($referEntity, 'getLayout')) {
                    $interface = $this->interfaceHelper->generate($classname);
                    if (!empty($interface['search']) && $interface['search'] && !empty($interface['name'])) {
                        $layoutEntities[] = $interface['name'];
                        $interfaces[$interface['name']] = $interface['name'];
                    }
                }
            }

            $metasData = $this->entityManager->getClassMetadata(Layout::class);
            $mappings = $metasData->getAssociationMappings();

            foreach ($mappings as $mapping) {
                $getter = 'get' . ucfirst($mapping['fieldName']);
                if (in_array($mapping['fieldName'], $layoutEntities) && method_exists($layout, $getter) && !empty($layout->$getter())) {
                    $entity[0] = $layout->$getter();
                    $entity['interfaceName'] = $interfaces[$mapping['fieldName']];
                    return $entity;
                }
            }
        }
    }

    /**
     * Get url infos
     *
     * @param mixed $findEntity
     * @param string $interfaceName
     * @return array
     */
    private function getInfos($findEntity, string $interfaceName)
    {
        $infos = [];
        $entity = NULL;

        if ($findEntity instanceof Media) {
            $infos['entity'] = $findEntity;
            $infos['url'] = $this->uploadDirname . $findEntity->getFilename();
            $infos['interfaceName'] = $interfaceName;
            $infos['infos'] = NULL;
        } elseif ($findEntity instanceof Block) {
            $entity = $this->entityManager->getRepository(Page::class)->findByBlock($findEntity);
            $interfaceName = $this->interfaceHelper->generate(Page::class)['name'];
        } else {
            $entity = $findEntity;
        }

        if ($entity && !empty($this->sitemap[$interfaceName][$entity->getId()])) {

            $translation = $this->translator->trans('singular', [], 'entity_' . $interfaceName);
            $label = $translation !== 'singular' ? $translation : ucfirst($interfaceName);
            $entityInfos = $this->sitemap[$interfaceName][$entity->getId()];

            $infos['entity'] = $entity;
            $infos['infos'] = $entityInfos;
            $infos['url'] = $entityInfos['url'];
            $infos['label'] = $label;
            $infos['update'] = $entityInfos['update'];
            $infos['interfaceName'] = $interfaceName;
        }

        return $infos;
    }

    /**
     * Get order key for array results
     *
     * @param string $orderBy
     * @param array $infos
     * @param array $entityResult
     * @return int
     */
    private function getOrderKey(string $orderBy, array $infos, array $entityResult)
    {
        $entity = $entityResult[0];
        $score = $entityResult['score'];

        if (preg_match('/date/', $orderBy) && $infos['interfaceName'] === 'newscast') {
            $publicationDate = $entity->getPublicationStart();
            return intval($publicationDate->format('YmdHis'));
        } else {
            return intval(str_replace('.', '', $score));
        }
    }

    /**
     * Set group item
     *
     * @param int $page
     * @param array $infos
     * @param string $orderBy
     * @param int $orderKey
     * @param array $infosRow
     * @return bool
     */
    private function setGroups(int $page, array $infos, string $orderBy, int $orderKey, array $infosRow)
    {
        $existing = !empty($this->results[$page]['items'][$infos['interfaceName']][$orderKey]);

        $this->results[$page]['items'][$infos['interfaceName']][$orderKey] = $infosRow;

        if ($orderBy === "date-desc") {
            krsort($this->results[$page]['items'][$infos['interfaceName']]);
        } elseif ($orderBy === "date-asc" || $orderBy === "score") {
            ksort($this->results[$page]['items'][$infos['interfaceName']]);
        }

        return $existing;
    }

    /**
     * Set item
     *
     * @param int $page
     * @param int $orderKey
     * @param array $infosRow
     * @param string $orderBy
     * @return bool
     */
    private function setItems(int $page, int $orderKey, array $infosRow, string $orderBy)
    {
        $existing = !empty($this->results[$page]['items'][$orderKey]);

        $this->results[$page]['items'][$orderKey] = $infosRow;

        if ($orderBy === "date-desc") {
            krsort($this->results[$page]['items']);
        } elseif ($orderBy === "date-asc" || $orderBy === "score") {
            ksort($this->results[$page]['items']);
        }

        return $existing;
    }

    /**
     * Get counts
     *
     * @param Search $search
     * @return array
     */
    private function getCounts(Search $search)
    {
        $counters = [];
        $all = 0;

        foreach ($this->results as $key => $results) {

            foreach ($results as $keyName => $items) {

                if ($search->getFilterGroup()) {
                    foreach ($items as $key => $item) {
                        $previousCount = !empty($counters[$key]) ? $counters[$key] : 0;
                        $counters[$key] = $previousCount > 0 ? $previousCount + count($item) : count($item);
                        $all = count($item) + $all;
                        $counters['all'] = $all;
                    }
                } else {
                    $previousCount = !empty($counters[$keyName]) ? $counters[$keyName] : 0;
                    $counters[$keyName] = $previousCount > 0 ? $previousCount + count($items) : count($items);
                    $all = count($items) + $all;
                    $counters['all'] = $all;
                }
            }
        }

        return $counters;
    }
}