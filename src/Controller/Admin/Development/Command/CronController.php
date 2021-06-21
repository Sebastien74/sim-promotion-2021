<?php

namespace App\Controller\Admin\Development\Command;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CronController
 *
 * To execute cron
 *
 * @Route("/admin-%security_token%/development/commands/cron-scheduler", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CronController extends BaseCommand
{
    /**
     * Execute cron
     *
     * @Route("/run", methods={"GET"}, name="admin_cron_scheluder")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function run(Request $request)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'scheduler:execute']);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return new JsonResponse(['success' => true, 'reload' => true]);
    }
}