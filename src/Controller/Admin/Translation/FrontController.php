<?php

namespace App\Controller\Admin\Translation;

use App\Controller\Admin\AdminController;
use App\Form\Manager\Translation\FrontManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FrontController
 *
 * To edit translations in front
 *
 * @Route("/admin-%security_token%/translations/front", schemes={"%protocol%"})
 * @IsGranted("ROLE_TRANSLATION")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontController extends AdminController
{
    /**
     * Edit text
     *
     * @Route("/edit-text", methods={"POST", "GET"}, name="front_translation_edit_text", options={"expose"=true})
     *
     * @param FrontManager $frontManager
     * @return JsonResponse
     */
    public function text(FrontManager $frontManager): JsonResponse
    {
        return new JsonResponse($frontManager->postText());
    }
    /**
     * Edit i18n
     *
     * @Route("/edit-i18n", methods={"POST"}, name="front_translation_edit_i18n", options={"expose"=true})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function i18n(Request $request): JsonResponse
    {
        die;
        return new JsonResponse(['success' => true]);
    }
}