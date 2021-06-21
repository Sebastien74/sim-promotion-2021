<?php

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BuildController
 *
 * Manage build page render
 *
 * @Route("/website/maintenance/status", schemes={"%protocol%"})
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BuildController extends FrontController
{
    /**
     * Build view
     *
     * @Route("/in-build", methods={"GET"}, name="website_in_build")
     *
     * @param Request $request
     * @return Response
     */
    public function build(Request $request)
    {
        return $this->render('core/page/build/view.html.twig', [
            'website' => $this->getWebsite($request)
        ]);
    }
}