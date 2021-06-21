<?php

namespace App\EventSubscriber;

use App\Event\WebsiteUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * WebsiteSubscriber
 *
 * https://symfony.com/doc/4.4/components/event_dispatcher.html
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteSubscriber implements EventSubscriberInterface
{
    /**
     * Get Events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            WebsiteUpdatedEvent::NAME => 'onUpdated',
        ];
    }

    /**
     * onUpdated
     *
     * @param WebsiteUpdatedEvent $event
     */
    public function onUpdated(WebsiteUpdatedEvent $event)
    {
        $event->stopPropagation();
    }
}