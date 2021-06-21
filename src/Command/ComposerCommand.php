<?php

namespace App\Command;

use Composer\Console\Application;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ComposerCommand
 *
 * To execute composer commands
 *
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ComposerCommand
{
    private $kernel;

    /**
     * ComposerCommand constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Execute cache:clear --env
     *
     * @return string $output->fetch()
     * @throws Exception
     */
    public function autoload(): string
    {
        try {

            $bootstrapDirname =  "/composer.phar/src/bootstrap.php";
            $bootstrapDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $bootstrapDirname);
            require_once "phar://" . $this->kernel->getProjectDir() . $bootstrapDirname;

            putenv("COMPOSER_HOME={$this->kernel->getProjectDir()}");
            putenv("OSTYPE=OS400"); //force to use php://output instead of php://stdout

            $application = new Application();
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'dump-autoload',
                '--no-dev' => true,
                '--classmap-authoritative' => true,
            ]);
            $application->run($input);

//            return $output->fetch();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
}