<?php

namespace App\Controller\Admin\Development\Command;

use App\Controller\Admin\AdminController;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BaseCommand
 *
 * Base commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseCommand extends AdminController
{
    /**
     * Result
     *
     * @Route("/result", methods={"GET"}, name="admin_command_result")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return Response
     * @throws Exception
     */
    public function result()
    {
        return $this->cache('admin/page/development/command.html.twig', [
            'disabledFlashBag' => true
        ]);
    }

    /**
     * Set Command FlashBag
     *
     * @param string $response
     * @param string $command
     */
    protected function setFlashBag(string $response, string $command)
    {
        $session = new Session();
        $session->getFlashBag()->add('command', [
            'dirname' => $this->kernel->getProjectDir(),
            'command' => 'php bin/console ' . $command,
            'output' => $response
        ]);
    }
}