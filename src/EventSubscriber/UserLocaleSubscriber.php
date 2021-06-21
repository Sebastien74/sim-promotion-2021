<?php

namespace App\EventSubscriber;

use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * UserLocaleSubscriber
 *
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleSubscriber afterwards.
 *
 * @property SessionInterface $session
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserLocaleSubscriber implements EventSubscriberInterface
{
    private $session;

    /**
     * UserLocaleSubscriber constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var User|UserFront $user */
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getLocale()) {
            $this->session->set('_locale', $user->getLocale());
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
