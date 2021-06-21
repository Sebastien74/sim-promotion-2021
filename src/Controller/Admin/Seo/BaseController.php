<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Seo\Model;
use App\Entity\Seo\Session;
use App\Entity\Seo\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * BaseController
 *
 * SEO base controller
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseController extends AdminController
{
    /**
     * Get Entities for tree
     *
     * @param Request $request
     * @param Website $website
     */
    protected function getEntities(Request $request, Website $website)
    {
        $currentUrl = !empty($this->arguments['currentUrl']) ? $this->arguments['currentUrl'] : NULL;
        $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metasData as $metaData) {

            $classname = $metaData->getName();
            $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;
            $entities = [];
            if ($classname !== Session::class && $baseEntity && method_exists($baseEntity, 'getUrls') && method_exists($baseEntity, 'getWebsite')) {
                $entities = $this->entityManager->getRepository($classname)->createQueryBuilder('e')
                    ->leftJoin('e.website', 'w')
                    ->leftJoin('e.urls', 'u')
                    ->leftJoin('u.seo', 's')
                    ->andWhere('e.website = :website')
                    ->andWhere('u.locale = :locale')
                    ->setParameter('website', $website)
                    ->setParameter('locale', $request->get('entitylocale'))
                    ->addSelect('w')
                    ->addSelect('u')
                    ->addSelect('s')
                    ->getQuery()
                    ->getResult();
            }

            foreach ($entities as $entity) {
                foreach ($entity->getUrls() as $url) {
                    if ($url instanceof Url) {
                        if ($url->getLocale() === $request->get('entitylocale')) {
                            $this->getUrls($url, $entity, $currentUrl);
                        }
                    }
                }
            }
        }

        $this->getModels($request, $website);

        if (!empty($this->arguments['entities']['page'])) {
            $this->arguments['entities']['page'] = $this->getTree($this->arguments['entities']['page']);
        }
    }

    /**
     * Get all Url
     *
     * @param Url $url
     * @param mixed $entity
     * @param Url|NULL $currentUrl
     */
    private function getUrls(Url $url, $entity, Url $currentUrl = NULL)
    {
        $interfaceName = $entity::getInterface()['name'];

        if ($url === $currentUrl) {
            $this->arguments['currentCategory'] = $interfaceName;
        }

        $classname = $this->entityManager->getClassMetadata(get_class($entity))->getName();

        if (empty($this->arguments['mappedEntities']) || !in_array($classname, $this->arguments['mappedEntities'])) {
            $this->arguments['mappedEntities'][] = $classname;
        }

        $this->arguments['entities'][$interfaceName][] = (object)[
            'classname' => $classname,
            'title' => $entity->getAdminName(),
            'url' => $url,
            'active' => $url === $currentUrl,
            'seo' => $url->getSeo(),
            'entity' => $entity
        ];
    }

    /**
     * Get models
     *
     * @param Request $request
     * @param Website $website
     */
    private function getModels(Request $request, Website $website)
    {
        $repository = $this->entityManager->getRepository(Model::class);
        $mappedEntities = !empty($this->arguments['mappedEntities']) ? $this->arguments['mappedEntities'] : [];

        foreach ($mappedEntities as $classname) {

            $interface = $this->getInterface($classname);

            if (isset($interface['configuration']) && $interface['configuration']->asCard) {

                $model = $repository->findOneBy([
                    'website' => $website,
                    'className' => $classname,
                    'locale' => $request->get('entitylocale')
                ]);

                if (!$model) {
                    $model = new Model();
                    $model->setLocale($request->get('entitylocale'))
                        ->setClassName($classname)
                        ->setWebsite($website);
                    $this->entityManager->persist($model);
                    $this->entityManager->flush();
                }

                $this->arguments['models'][$model->getId()] = $model;

                ksort($this->arguments['models']);
            }
        }
    }
}