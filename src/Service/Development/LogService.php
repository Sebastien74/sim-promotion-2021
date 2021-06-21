<?php

namespace App\Service\Development;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * LogService
 *
 * To copy file from path to other path
 *
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogService
{
    private $kernel;

    /**
     * LogService constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Log
     *
     * @param string $name
     * @param int $type
     * @param string $filename
     * @param string $message
     */
    public function log(string $name, int $type, string $filename, string $message)
    {
        $logger = new Logger($name);
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/' . $filename, 10, $type));

        if ($type === Logger::CRITICAL) {
            $logger->critical($message);
        } elseif ($type === Logger::WARNING) {
            $logger->warning($message);
        } else {
            $logger->info($message);
        }
    }
}