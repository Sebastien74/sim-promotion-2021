<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LogoutListener
 *
 * To manage events on user logout
 *
 * @property TranslatorInterface $translator
 * @property TokenStorageInterface $tokenStorage
 * @property RouterInterface $router
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogoutListener
{
    private $translator;
    private $tokenStorage;
    private $router;

    /**
     * LogoutListener constructor.
     *
     * @param TranslatorInterface $translator
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     */
    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     * On logout success
     *
     * @param LogoutEvent $event
     */
    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $request = $event->getRequest();

        if ($request->get('reset_password')) {
            $routeName = $request->get('front') ? 'security_front_password_request' : 'security_password_request';
            $response = new RedirectResponse($this->router->generate($routeName) . '?expire=true');
            $this->invalidate($request, $response);
        } elseif ($request->get('inactive')) {
            $routeName = $request->get('front') ? 'security_front_login' : 'security_login';
            $response = new RedirectResponse($this->router->generate($routeName) . '?inactive=true');
            $this->invalidate($request, $response);
        } elseif ($request->get('validation')) {
            $routeName = $request->get('front') ? 'security_front_register' : 'security_register';
            $response = new RedirectResponse($this->router->generate($routeName) . '?validation=true');
            $this->invalidate($request, $response);
        }

        $response = new RedirectResponse($this->router->generate('front_index'));
        $this->invalidate($request, $response);
    }

    /**
     * Invalidate User Session
     *
     * @param Request $request
     * @param RedirectResponse $response
     */
    private function invalidate(Request $request, RedirectResponse $response)
    {
        $request->getSession()->invalidate();
        $this->tokenStorage->setToken(NULL);

        foreach ($request->cookies as $cookieName => $value) {
            $response->headers->clearCookie($cookieName);
        }

        $response->send();
    }
}