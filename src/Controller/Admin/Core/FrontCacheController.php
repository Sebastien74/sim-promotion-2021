<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\SeoConfiguration;
use App\Form\Type\Seo\Configuration\ConfigurationType;
use App\Service\Core\CacheService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FrontCacheController
 *
 * Front Cache management
 *
 * @Route("/admin-%security_token%/{website}/core/cache", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property SeoConfiguration $class
 * @property ConfigurationType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontCacheController extends AdminController
{
    /**
     * Clear cache files
     *
     * @Route("/clear-files", methods={"DELETE", "GET"}, name="admin_clear_front_cache")
     *
     * @param Request $request
     * @param CacheService $cacheService
     * @return JsonResponse
     */
    public function clearCache(Request $request, CacheService $cacheService)
    {
        $cacheService->clearFrontCache($this->getWebsite($request));

        if ($request->get('referer')) {
            $session = new Session();
            $session->getFlashBag()->add('success', $this->translator->trans('Cache supprimé avec succès !!', [], 'admin'));
        }

        return new JsonResponse(['success' => true, 'reload' => true]);
    }
}