<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\Url;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ServiceController
 *
 * Admin service management
 *
 * @Route("/admin-%security_token%/services", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ServiceController extends AdminController
{
    /**
     * Code generator
     *
     * @Route("/code-generator/{string}/{url}/{classname}/{entityId}",
     *     defaults={"string": null, "url": null, "classname": null, "entityId": null},
     *     methods={"GET"},
     *     name="admin_code_generator",
     *     options={"expose"=true})
     *
     * @param Request $request
     * @param SeoService $seoService
     * @param string|null $string $string
     * @param Url|null $url
     * @param string|null $classname
     * @param int|null $entityId
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function generateCode(Request $request, SeoService $seoService, string $string = NULL, Url $url = NULL, string $classname = NULL, int $entityId = NULL)
    {
        if (!$url instanceof Url && $string === 'undefined') {
            return new JsonResponse(['code' => '']);
        }

        if ($url instanceof Url) {

            $request->setLocale($url->getLocale());
            $request->getSession()->set('_locale', $url->getLocale());

            $entity = $this->entityManager->getRepository(urldecode($classname))->find($entityId);
            $seo = $seoService->execute($url, $entity, $url->getLocale());
            $string = is_array($seo) && !empty($seo['title']) ? $seo['title'] : $string;

            $request->setLocale($url->getWebsite()->getConfiguration()->getLocale());
            $request->getSession()->set('_locale', $url->getWebsite()->getConfiguration()->getLocale());
        }

        return new JsonResponse(['code' => Urlizer::urlize($string)]);
    }
}