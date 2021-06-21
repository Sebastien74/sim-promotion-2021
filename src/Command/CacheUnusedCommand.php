<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CacheUnusedCommand
 *
 * To run Cache clear all command
 *
 * @property KernelInterface $kernel
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CacheUnusedCommand extends Command
{
    protected static $defaultName = 'app:cache:clear:unused';

    private $kernel;
    private $io;

    /**
     * CacheUnusedCommand constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('To clear all unused cache repositories.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): string
    {
        $this->io = new SymfonyStyle($input, $output);

        $preservedDirs = ['prod', 'dev'];
        $cacheDirname = $this->kernel->getProjectDir() . '\var\cache';
        $cacheDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cacheDirname);
        $finder = new Finder();
        $filesystem = new Filesystem();
        $cacheDirs = [];

        foreach ($finder->in($cacheDirname) as $file) {
            $matches = explode('\\', $file->getPath());
            if (end($matches) === 'cache' && is_dir($file->getPathname())) {
                if (!in_array($file->getFilename(), $preservedDirs)) {
                    $cacheDirs[] = [
                        'path' => $file->getPath(),
                        'pathname' => $file->getPathname(),
                        'filename' => $file->getFilename()
                    ];
                }
            }
        }

        foreach ($cacheDirs as $cacheDir) {
            $dirMatches = explode('\\', $cacheDir['pathname']);
            $tmpDirname = $cacheDir['path'] . '\\' . uniqid() . '-' . end($dirMatches);
            $filesystem->rename($cacheDir['pathname'], $tmpDirname);
            $filesystem->remove($tmpDirname);
        }

        $message = 'All unused cache repositories successfully deleted.';
        $this->io->block($message, 'OK', 'fg=black;bg=green', ' ', true);

        return $message;
    }
}