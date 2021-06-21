<?php

namespace App\Controller\Front\Module;

use App\Entity\Core\Website;
use App\Service\Content\CryptService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CryptController
 *
 * Manage string encryption
 *
 * @Route("/cms/front/crypt", schemes={"%protocol%"})
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CryptController extends AbstractController
{
    /**
     * Encrypt
     *
     * @Route("/encrypt/{website}/{string}", methods={"GET"}, name="front_encrypt", options={"expose"=true})
     *
     * @param CryptService $cryptService
     * @param Website $website
     * @param string $string
     *
     * @return JsonResponse
     */
    public function encrypt(CryptService $cryptService, Website $website, $string)
    {
        return new JsonResponse(array('result' => $cryptService->execute($website, $string, 'e')));
    }

    /**
     * Decrypt
     *
     * @Route("/decrypt/{website}/{string}", methods={"GET"}, name="front_decrypt", options={"expose"=true})
     *
     * @param CryptService $codeService
     * @param Website $website
     * @param string $string
     *
     * @return JsonResponse
     */
    public function decrypt(CryptService $codeService, Website $website, $string)
    {
        return new JsonResponse(array('result' => $codeService->execute($website, $string, 'd')));
    }
}