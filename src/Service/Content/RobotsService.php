<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Seo\Session;
use App\Entity\Seo\Url;
use App\Helper\Core\InterfaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * RobotsServices
 *
 * Manage robots.txt
 *
 * @property EntityManagerInterface $entityManager
 * @property InterfaceHelper $interfaceHelper
 * @property SitemapService $sitemapService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RobotsService
{
    private $entityManager;
    private $interfaceHelper;
    private $sitemapService;

    /**
     * RobotsService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param InterfaceHelper $interfaceHelper
     * @param SitemapService $sitemapService
     */
    public function __construct(EntityManagerInterface $entityManager, InterfaceHelper $interfaceHelper, SitemapService $sitemapService)
    {
        $this->entityManager = $entityManager;
        $this->interfaceHelper = $interfaceHelper;
        $this->sitemapService = $sitemapService;
    }

    /**
     * Execute robots service
     *
     * @param Website $website
     * @return array
     * @throws Exception
     */
    public function execute(Website $website): array
    {
        return [
            'disallow' => !$website->getConfiguration()->getSeoStatus(),
            'noIndexes' => $this->getNoIndexes($website)
        ];
    }

    /**
     * Get noIndex urls
     *
     * @param Website $website
     * @return array
     * @throws Exception
     */
    private function getNoIndexes(Website $website): array
    {
        $urls = [];
        $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $this->sitemapService->setVars($website, 'fr');

        foreach ($metasData as $metaData) {

            $classname = $metaData->getName();
            $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;

            if ($classname !== Session::class && $baseEntity && method_exists($baseEntity, 'getUrls') && method_exists($baseEntity, 'getWebsite')) {

				$interface = $this->interfaceHelper->generate($classname);
				$entities = $this->sitemapService->getEntities($classname, $baseEntity, true);
				$entities = $this->sitemapService->getEntities($classname, $baseEntity, false, $entities);

                foreach ($entities as $entity) {
					$entityArray = $entity['array'];
					$entityObject = $entity['object'];
                    foreach ($entityArray['urls'] as $url) {
                        if (!empty($url['isIndex']) && $url['isIndex']) {
                            if ($entityObject instanceof Page && !$entityArray['isIndex']) {
                                if($entityArray['infill'] || !$url['isIndex']) {
                                    $urls[] = $this->sitemapService->setPage($entityArray, $url);
                                }
                            } else if (!$url['isIndex']) {
                                $urls[] = $this->sitemapService->setAsCard($entityArray, $entityObject, $interface, $url);
                            }
                        }
                    }
                }
            }
        }

        return $urls;
    }
}