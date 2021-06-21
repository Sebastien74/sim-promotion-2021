<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\DebugCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DebugController
 *
 * To execute debug commands
 *
 * @Route("/admin-%security_token%/development/commands/debug", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DebugController extends BaseCommand
{
    /**
     * Debug
     *
     * @Route("/debug/{service}", methods={"GET"}, name="bin_debug")
     *
     * @param Request $request
     * @param DebugCommand $cmd
     * @param string $service
     * @return RedirectResponse
     */
    public function debug(Request $request, DebugCommand $cmd, string $service)
    {
        $this->setFlashBag($cmd->debug($service), 'debug:' . $service);
        return $this->redirectToRoute('admin_command_result');
    }
}