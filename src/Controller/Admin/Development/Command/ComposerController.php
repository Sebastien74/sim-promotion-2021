<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\ComposerCommand;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ComposerController
 *
 * To execute debug commands
 *
 * @Route("/admin-%security_token%/development/commands/composer", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ComposerController extends BaseCommand
{
    /**
     * Autoload
     *
     * @Route("/autoload", methods={"GET"}, name="composer_autoload")
     *
     * @param Request $request
     * @param ComposerCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function autoload(Request $request, ComposerCommand $cmd)
    {
        $this->setFlashBag($cmd->autoload(), 'dump-autoload --no-dev --classmap-authoritative');
        return $this->redirect($request->headers->get('referer'));
    }
}