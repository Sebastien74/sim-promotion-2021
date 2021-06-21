<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * BaseCommand
 *
 * Base commands
 *
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseCommand
{
    protected $kernel;

    /**
     * JsRoutingCommand constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    protected function execute(array $params): string
    {
        try {

            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput($params);
            $output = new BufferedOutput();
            $application->run($input, $output);

            return $output->fetch();

        } catch (\Exception $exception) {

            return $exception->getMessage() . '<br>' . $exception->getTraceAsString();
        }
    }
}