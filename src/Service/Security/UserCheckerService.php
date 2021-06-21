<?php

namespace App\Service\Security;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * UserCheckerService
 *
 * To listen user
 *
 * @property TokenStorageInterface $tokenStorage
 * @property EntityManagerInterface $entityManager
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property RouterInterface $router
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserCheckerService
{
	private $tokenStorage;
	private $entityManager;
	private $authorizationChecker;
	private $router;

	/**
	 * UserListener constructor.
	 *
	 * @param TokenStorageInterface $tokenStorage
	 * @param EntityManagerInterface $entityManager
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 * @param RouterInterface $router
	 */
	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $entityManager,
		AuthorizationCheckerInterface $authorizationChecker,
		RouterInterface $router)
	{
		$this->tokenStorage = $tokenStorage;
		$this->entityManager = $entityManager;
		$this->authorizationChecker = $authorizationChecker;
		$this->router = $router;
	}

	/**
	 * To execute service
	 *
	 * @throws Exception
	 */
	public function execute(RequestEvent $event)
	{
		$request = $event->getRequest();
		$uri = $request->getUri();
		$token = $this->tokenStorage->getToken();

		if ($token && !$this->authorizationChecker->isGranted('IS_IMPERSONATOR')) {
			$user = $token->getUser();
			if ($user instanceof User || $user instanceof UserFront) {
				$this->checkAccount($event, $user);
				$this->checkPassword($event, $user, $uri);
				$this->setLastActivity($user);
			}
		}
	}

	/**
	 * To redirect inactive User
	 *
	 * @param RequestEvent $event
	 * @param $user
	 */
	private function checkAccount(RequestEvent $event, $user)
	{
		$session = $event->getRequest()->getSession();
		$frontUser = !$user instanceof User;

		if ($session->get('user_security_post')) {
			$response = new RedirectResponse($this->router->generate('security_logout') . '?validation=true&front=' . $frontUser, 302);
			$event->setResponse($response);
		} elseif (method_exists($user, 'getActive') && !$user->getActive()) {
			$response = new RedirectResponse($this->router->generate('security_logout') . '?inactive=true&front=' . $frontUser, 302);
			$event->setResponse($response);
		}
	}

	/**
	 * Check if User must reset his password
	 *
	 * @param RequestEvent $event
	 * @param User|UserFront $user
	 * @param string $uri
	 * @return void
	 * @throws Exception
	 */
	private function checkPassword(RequestEvent $event, $user, string $uri): void
	{
		if (method_exists($user, 'getResetPasswordDate')) {

			$delay = NULL;
			$repository = $this->entityManager->getRepository(Website::class);
			$website = $repository->findOneByHost($event->getRequest()->getHost(), true, true);
			$website = $website ?: $repository->findAllArray()[0];
			$security = !empty($website['security']) ? $website['security'] : NULL;
			$securityStatus = $user instanceof User ? $security['adminPasswordSecurity'] : $security['frontPasswordSecurity'];

			if ($security) {
				$delay = $user instanceof User ? $security['adminPasswordDelay'] : $security['frontPasswordDelay'];
			}

			if ($delay) {

				if (!$user->getResetPasswordDate()) {
					$user->setResetPasswordDate(new DateTime('now'));
					$this->entityManager->persist($user);
					$this->entityManager->flush();
				}

				$today = new DateTime();
				$interval = new DateInterval('P' . $delay . 'D');

				/** @var DateTime $resetDate */
				$resetDate = $user->getResetPasswordDate();
				$resetDate->add($interval);

				if ($securityStatus && $today > $resetDate && preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri)) {
					$frontUser = !$user instanceof User;
					$user->setResetPassword(true);
					$this->entityManager->persist($user);
					$this->entityManager->flush();
					$response = new RedirectResponse($this->router->generate('security_logout') . '?reset_password=true&front=' . $frontUser, 302);
					$event->setResponse($response);
				}
			}
		}
	}

	/**
	 * To set last activity
	 *
	 * @param User|UserFront $user
	 */
	private function setLastActivity($user)
	{
		$delay = new \DateTime();
		$delay->setTimestamp(strtotime('2 minutes ago'));

		if ($user->getLastActivity() < $delay) {
			$user->setLastActivity(new DateTime('now'));
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		}

//            if (!$user instanceof User) {
//
//                $request = $event->getRequest();
//                $analyticService = $this->subscriber->get(AnalyticService::class);
//                $tokenSession = $analyticService->setUserTokenSession();
//                $ip = $analyticService->setIP();
//                $anonymize = md5($ip);
//
//                $website = $request->getSession()->get('frontWebsite')
//                    ? $request->getSession()->get('frontWebsite')
//                    : $this->entityManager->getRepository(Website::class)->findOneByHost($request->getHost());
//                /** @var Session $session */
//                $session = $this->entityManager->getRepository(Session::class)->findByAnonymizeAndSession($anonymize, $website, $tokenSession);
//
//                if($session && $session->getLastActivity() < $delay) {
//                    $session->setLastActivity(new DateTime('now'));
//                    $this->entityManager->persist($session);
//                    $this->entityManager->flush();
//                }
//
//                if($request->get('_route') === 'front_activity' && $session) {
//                    $sessionUrl = $this->entityManager->getRepository(SessionUrl::class)->findLastByUri($session, json_decode($request->get('uri')));
//                    if($sessionUrl instanceof SessionUrl) {
//                        $sessionUrl->setLeavedAt(new DateTime('now'));
//                        $this->entityManager->persist($sessionUrl);
//                        $this->entityManager->flush();
//                    }
//                }
//            }
	}
}