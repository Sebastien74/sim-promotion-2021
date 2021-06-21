<?php

namespace App\Controller\Front\Module;

use App\Controller\Front\FrontController;
use App\Entity\Core\Website;
use App\Service\Content\LocaleService;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LocaleController
 *
 * Front Locale switcher render & management
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LocaleController extends FrontController
{
    /**
     * Locale switcher View
     *
     * @Route("/html/render/locales/switcher/view", methods={"GET"}, name="front_locales_switcher", options={"expose"=true}, schemes={"%protocol%"})
     *
     * @param LocaleService $localeService
     * @param Website $website
     * @param string|null $class
     * @return Response
     * @throws Exception
     */
    public function switcher(LocaleService $localeService, Website $website, string $class = NULL)
    {
        return $this->cache('front/' . $website->getConfiguration()->getTemplate() . '/include/locale-switcher.html.twig', $website, [
            'class' => urldecode($class),
            'website' => $website,
            'configuration' => $website->getConfiguration(),
            'routes' => $localeService->execute($website)
        ]);
    }
}