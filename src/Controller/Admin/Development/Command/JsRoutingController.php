<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\JsRoutingCommand;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * JsRoutingController
 *
 * To execute fos js-routing commands
 *
 * @Route("/admin-%security_token%/development/commands/js-routing", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class JsRoutingController extends BaseCommand
{
    /**
     * Generate js routes
     *
     * @Route("/dump", methods={"GET"}, name="js_routing_dump")
     *
     * @param Request $request
     * @param JsRoutingCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function dump(Request $request, JsRoutingCommand $cmd)
    {
        $this->setFlashBag($cmd->dump(), 'fos:js-routing:dump');
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Debug js routes
     *
     * @Route("/debug", methods={"GET"}, name="js_routing_debug")
     *
     * @param Request $request
     * @param JsRoutingCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function debug(Request $request, JsRoutingCommand $cmd)
    {
        $this->setFlashBag($cmd->debug(), 'fos:js-routing:debug');
        return $this->redirect($request->headers->get('referer'));
    }
}