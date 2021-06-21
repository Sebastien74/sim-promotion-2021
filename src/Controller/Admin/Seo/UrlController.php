<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\Url;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UrlController
 *
 * SEO Url management
 *
 * @Route("/admin-%security_token%/{website}/seo/urls", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlController extends AdminController
{
    /**
     * Url status
     *
     * @Route("/status/{url}", methods={"GET"}, name="admin_url_status", options={"expose"=true})
     *
     * @param Url $url
     * @return JsonResponse
     */
    public function status(Url $url)
    {
        $newStatus = $url->getIsOnline() ? false : true;

        $url->setIsOnline($newStatus);
        $this->entityManager->persist($url);
        $this->entityManager->flush();

        return new JsonResponse(['status' => $newStatus ? 'online' : 'offline']);
    }
}