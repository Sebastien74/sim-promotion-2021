<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\AssetsCommand;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AssetsController
 *
 * To execute assets commands
 *
 * @Route("/admin-%security_token%/development/commands/assets", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AssetsController extends BaseCommand
{
    /**
     * Install assets
     *
     * @Route("/install", methods={"GET"}, name="assets_install")
     *
     * @param Request $request
     * @param AssetsCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function install(Request $request, AssetsCommand $cmd)
    {
        $this->setFlashBag($cmd->install(), 'assets:install --symlink --relative web');
        return $this->redirect($request->headers->get('referer'));
    }
}