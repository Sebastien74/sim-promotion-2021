<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Form\Manager\Seo\UrlManager;
use App\Twig\Core\AppRuntime;
use Exception;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ArchiveController
 *
 * SEO archive URL management
 *
 * @Route("/admin-%security_token%/{website}/seo/archive")
 * @IsGranted("ROLE_SEO")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ArchiveController extends AdminController
{
    /**
     * Index archive
     *
     * @Route("/index", name="admin_archive_index")
     *
     * @param Website $website
     * @param AppRuntime $appRuntime
     * @return Response
     */
    public function archive(Website $website, AppRuntime $appRuntime)
    {
        $this->disableProfiler();

        $archive = $this->getArchive($website, $appRuntime);

        return $this->cache('admin/page/seo/archive.html.twig', [
            'archive' => $archive
        ]);
    }

    /**
     * Restore Entity
     *
     * @Route("/restore/{classname}/{id}", methods={"GET"}, name="admin_url_archive_restore")
     *
     * @param Request $request
     * @param UrlManager $urlManager
     * @return RedirectResponse
     */
    public function restore(Request $request, UrlManager $urlManager)
    {
        $website = $this->getWebsite($request);
        $classname = urldecode($request->get('classname'));
        $entity = $this->entityManager->getRepository($classname)->find($request->get('id'));

        if (is_object($entity) && method_exists($entity, 'getUrls')) {
            foreach ($entity->getUrls() as $url) {
                /** @var Url $url */
                $existingUrl = $urlManager->getExistingUrl($url, $website, $entity);
                $code = $existingUrl && $existingUrl->getId() !== $url->getId() ? $url->getCode() . '-' . uniqid() : $url->getCode();
                $url->setCode($code);
                $url->setIsArchived(false);
                if ($url->getCode()) {
                    $url->setIsOnline(true);
                }
                $this->entityManager->persist($url);
            }
        }

        $queryBuilder = $this->entityManager->getRepository($classname)->createQueryBuilder('e')
            ->leftJoin('e.urls', 'u')
            ->andWhere('u.isArchived = :isArchived')
            ->andWhere('u.website = :website')
            ->setParameter('isArchived', false)
            ->setParameter('website', $website)
            ->addSelect('u');

        if (method_exists($entity, 'getLevel')) {
            $queryBuilder->andWhere('e.level = :level')->setParameter('level', 1);
        }
        if (method_exists($entity, 'setLevel')) {
            $entity->setLevel(1);
        }
        if (method_exists($entity, 'setParent')) {
            $entity->setParent(NULL);
        }

        $entity->setPosition(count($queryBuilder->getQuery()->getResult()) + 1);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Delete Entity
     *
     * @Route("/delete/{classname}/{id}", methods={"DELETE"}, name="admin_url_archive_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        $classname = urldecode($request->get('classname'));
        $entity = $this->entityManager->getRepository($classname)->find($request->get('id'));
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Initialize archive
     *
     * @param Website $website
     * @param AppRuntime $appRuntime
     * @return array
     */
    private function getArchive(Website $website, AppRuntime $appRuntime)
    {
        $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $archive = [];
        $ids = [];

        foreach ($metasData as $metaData) {

            $classname = $metaData->getName();
            $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;
            $entities = $baseEntity && method_exists($baseEntity, 'getUrls') ?
                $this->entityManager->getRepository($classname)->findBy(['website' => $website]) : [];

            foreach ($entities as $entity) {
                foreach ($entity->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url instanceof Url && $url->getIsArchived()) {
                        $interface = $this->getInterface($classname);
                        if (empty($ids[$interface['name']][$entity->getId()])) {
                            $pluralTrans = $this->translator->trans('plural', [], 'entity_' . $interface['name']);
                            $keyName = $pluralTrans !== 'plural' ? $pluralTrans : ucfirst($interface['name']);
                            $deleteTrans = $this->translator->trans('delete', [], 'delete_' . $interface['name']);
                            $restoreTrans = $this->translator->trans('restore', [], 'restore_' . $interface['name']);
                            $uri = $interface['classname'] === Page::class ? '/' . $url->getCode()
                                : ($appRuntime->routeExist('front_' . $interface['name'] . '_view_only.' . $url->getLocale())
                                    ? $this->generateUrl('front_' . $interface['name'] . '_view_only.' . $url->getLocale(), ['url' => $url->getCode()]) : NULL);
                            $archive[$keyName][] = [
                                'entity' => $entity,
                                'uri' => $uri,
                                'delete' => $deleteTrans !== 'delete' ? $deleteTrans : $this->translator->trans('Supprimer', [], 'admin'),
                                'restore' => $restoreTrans !== 'restore' ? $restoreTrans : $this->translator->trans('Restaurer', [], 'admin'),
                                'interface' => $interface
                            ];
                            $ids[$interface['name']][$entity->getId()] = true;
                        }
                    }
                }
            }
        }

        ksort($archive);

        return array_reverse($archive);
    }
}