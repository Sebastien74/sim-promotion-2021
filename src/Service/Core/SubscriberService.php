<?php

namespace App\Service\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SubscriberService
 *
 * To inject service in container
 *
 * @property ContainerInterface $container
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SubscriberService
{
    private $container;

    /**
     * SubscriberService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * To get service
     *
     * @param string $className
     * @return object
     */
    public function get(string $className)
    {
        return $this->container->get($className);
    }
}