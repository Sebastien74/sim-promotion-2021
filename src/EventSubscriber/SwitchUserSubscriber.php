<?php

namespace App\EventSubscriber;

use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * SwitchUserSubscriber
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * On switch User Event
     *
     * @param SwitchUserEvent $event
     * @return RedirectResponse
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        /** @var User|UserFront $user */
        $user = $event->getTargetUser();
        $request = $event->getRequest();

        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set('_locale', $user->getLocale());
        }

        $previousUrl = $request->getSession()->get('last_security_uri');

        if ($user instanceof UserFront && $previousUrl) {
            return new RedirectResponse($previousUrl);
        } else {
            return new RedirectResponse($request->getSchemeAndHttpHost());
        }
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            /** constant for security.switch_user */
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}