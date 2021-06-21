<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\User;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ProfileController
 *
 * Security User Profile management
 *
 * @Route("/admin-%security_token%/{website}/security/users", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileController extends AdminController
{
    /**
     * @Route("/profile/{user}", methods={"GET"}, defaults={"user": null}, name="admin_user_profile", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $authChecker
     * @param User|null $user
     * @return Response
     * @throws Exception
     */
    public function profile(Request $request, AuthorizationCheckerInterface $authChecker, User $user = null)
    {
        $user = empty($user) ? $this->getUser() : $user;

        if ($user->getId() !== $this->getUser()->getId() && !$authChecker->isGranted('ROLE_INTERNAL')) {
            throw $this->createAccessDeniedException($this->translator->trans('Accès refusé.', [], 'security_cms'));
        }

        return $this->cache('admin/page/security/profile.html.twig', [
            "user" => $user
        ]);
    }
}