<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Repository\Security\UserRepository;
use App\Service\Core\KeyGeneratorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UtilityController
 *
 * Security utilities management
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UtilityController extends AdminController
{
    /**
     * @Route("/admin-%security_token%/security/utility", methods={"GET", "POST"}, name="admin_security_utility", schemes={"%protocol%"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserRepository $userRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsersApi(UserRepository $userRepository, Request $request)
    {
        return $this->json(
            $userRepository->findAllMatching($request->query->get('query')),
            200, [],
            ['groups' => ['main']]
        );
    }

    /**
     * Password generator
     *
     * @Route("/admin-%security_token%/security/utility/password-generator", methods={"GET"}, name="security_password_generator", options={"expose"=true}, schemes={"%protocol%"})
     *
     * @param KeyGeneratorService $keyGeneratorService
     * @return JsonResponse
     */
    public function passwordGenerator(KeyGeneratorService $keyGeneratorService)
    {
        return new JsonResponse(['password' => $keyGeneratorService->generate(4, 4, 4, 2)]);
    }
}