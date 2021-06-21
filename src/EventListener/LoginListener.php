<?php

namespace App\EventListener;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * LoginListener
 *
 * Listen User login event
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LoginListener
{
    private $entityManager;

    /**
     * LoginListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * onSecurityInteractiveLogin
     *
     * @param InteractiveLoginEvent $event
     * @throws Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!empty($user) && method_exists($user, "setLastLogin")) {
            $user->setLastLogin(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}