<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTENSIONS = '.{php,xml,yaml,yml}';

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Get public __DIR__
     */
    public function getPublicDir(): string
    {
        return $this->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
            $container->import('../config/{services}/*.yaml');
            if(is_file(\dirname(__DIR__) . '/.env')) {
                $container->import('../config/{packages}/env/' . $_ENV['APP_ENV_NAME'] . '/*.yaml');
            }
        } elseif (is_file($path = \dirname(__DIR__) . '/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__) . '/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}