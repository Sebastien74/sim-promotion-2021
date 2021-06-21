<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SwitcherController
 *
 * Users switcher management
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SwitcherController extends AdminController
{
    /**
     * User switcher view
     *
     * @Route("/admin-%security_token%/user-switcher/{website}/{type}", methods={"GET"}, name="admin_user_switcher", schemes={"%protocol%"}, options={"expose"=true})
     *
     * @param Website $website
     * @param string $type
     * @return JsonResponse
     */
    public function switcher(Website $website, string $type)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_SWITCH');

        return new JsonResponse(['html' => $this->renderView('security/switcher.html.twig', [
            'website' => $website,
            'inAdmin' => $type === 'admin',
            'users' => $this->entityManager->getRepository(User::class)->findForSwitcher()
        ])]);
    }
}