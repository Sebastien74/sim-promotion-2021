<?php

namespace App\Command;

use Symfony\Component\Filesystem\Filesystem;

/**
 * CacheCommand
 *
 * To execute cache commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CacheCommand extends BaseCommand
{
    /**
     * Execute cache:clear --env
     *
     * @param bool $hasFilesystem
     * @return string $output->fetch()
     */
    public function clear($hasFilesystem = false): string
    {
        if($hasFilesystem) {

            $filesystem = new Filesystem();
            $cacheDirname = $this->kernel->getCacheDir();
            $tmpDirname = $this->kernel->getProjectDir() . '/var/cache/__' . $this->kernel->getEnvironment();
            $tmpDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $tmpDirname);

            if ($filesystem->exists($tmpDirname)) {
                $filesystem->remove($tmpDirname);
            }

            if ($filesystem->exists($cacheDirname)) {
                $filesystem->rename($cacheDirname, $tmpDirname);
                $filesystem->remove($tmpDirname);
            }

            return 'Cache successfully cleared.';
        }

        return $this->execute([
            'command' => 'cache:clear',
            '--env' => $this->kernel->getEnvironment()
        ]);
    }
}