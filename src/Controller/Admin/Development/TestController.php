<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TestController
 *
 * Webmaster tools
 *
 * @Route("/admin-%security_token%/development/tester", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TestController extends AdminController
{
    /**
     * Test view
     *
     * @Route("/view/{website}", methods={"GET", "POST"}, name="admin_dev_test")
     *
     * @param Request $request
     * @param Website $website
     * @return Response
     * @throws Exception
     */
    public function test(Request $request, Website $website)
    {
        return $this->render('admin/page/development/test.html.twig', [

        ]);
    }
}