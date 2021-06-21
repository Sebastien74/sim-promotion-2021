<?php

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * ExtraLoader
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExtraLoader extends Loader
{
    private $kernel;
    private $isLoaded = false;

    /**
     * ExtraLoader constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $filesystem = new Filesystem();
        $moduleDirname = $this->kernel->getProjectDir() . '/src/Module';
        $moduleDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $moduleDirname);

        if ($filesystem->exists($moduleDirname)) {

            $routes = new RouteCollection();
            $finder = new Finder();
            $finder->in($moduleDirname)->name('routes.yaml');

            foreach ($finder as $file) {
                $importedRoutes = $this->import($file->getPathname(), 'yaml');
                $routes->addCollection($importedRoutes);
            }

            $this->isLoaded = true;

            return $routes;
        }

        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}