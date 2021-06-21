<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * DomainSessionStorage
 *
 * To set domain session storage
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainSessionStorage extends NativeSessionStorage
{
    /**
     * setOptions.
     *
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
			$options["cookie_domain"] = '.' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }

        return parent::setOptions($options);
    }
}