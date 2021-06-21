<?php

namespace App\EventListener;

use App\Entity\Core\Website;
use App\Service\Admin\TitleService;
use App\Service\Content\RedirectionService;
use App\Service\Core\LastRouteService;
use App\Service\Core\SubscriberService;
use App\Service\Security\UserCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * RequestListener
 *
 * Listen front events
 *
 * @property SubscriberService $subscriber
 * @property RedirectionService $redirectionService
 * @property RouterInterface $router
 * @property EntityManagerInterface $entityManager
 * @property RequestEvent $event
 * @property Request $request
 * @property Session $session
 * @property string $uri
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RequestListener
{
	private $subscriber;
	private $router;
	private $entityManager;
	private $redirectionService;
	private $event;
	private $request;
	private $session;
	private $uri;

	/**
	 * RequestListener constructor.
	 *
	 * @param SubscriberService $subscriber
	 * @param RouterInterface $router
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(
		SubscriberService $subscriber,
		RouterInterface $router,
		EntityManagerInterface $entityManager)
	{
		$this->subscriber = $subscriber;
		$this->router = $router;
		$this->entityManager = $entityManager;
	}

	/**
	 * onKernelRequest
	 *
	 * @param RequestEvent $event
	 * @throws NonUniqueResultException
	 */
	public function onKernelRequest(RequestEvent $event)
	{
		$this->redirectionService = $this->subscriber->get(RedirectionService::class);
		$this->event = $event;
		$this->request = $this->event->getRequest();
		$this->session = $this->request->getSession();
		$this->uri = $this->request->getUri();

		$isLogin = preg_match('/\/secure\/user\//', $this->uri);
		$isFront = !preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->uri) && !$isLogin || preg_match('/\/preview\//', $this->uri);

		if ($isFront && $this->request->isMethod('post')) {
			$this->session->set('frontPostProcess', true);
		}

		if (!$this->event->isMasterRequest()) {
			return;
		}

		if ($isFront) {
			$this->checkDisabledUris();
			$this->frontRequest();
		} else if (!$isLogin) {
			$this->adminRequest();
		}

		$this->subscriber->get(LastRouteService::class)->execute($event);
		$this->subscriber->get(UserCheckerService::class)->execute($event);
	}

	/**
	 * Check if is disabled URI
	 */
	private function checkDisabledUris()
	{
		if ($this->uri) {

			$disabledPatterns = [
				'wordpress',
				'wp-includes',
				'wp-admin',
				'autodiscover',
			];

			foreach ($disabledPatterns as $pattern) {
				if (preg_match('/' . $pattern . '/', $this->uri)) {
					$this->event->setResponse(new RedirectResponse($this->request->getSchemeAndHttpHost(), 301));
				}
			}
		}
	}

	/**
	 * Check front Request
	 *
	 * @throws NonUniqueResultException
	 */
	private function frontRequest()
	{
		$response = $this->redirectionService->execute($this->request);

		if ($response['urlRedirection'] && preg_match('/http/', $response['urlRedirection'])) {
			$this->event->setResponse(new RedirectResponse($response['urlRedirection'], 301));
		} elseif ($response['domainRedirection']) {
			$this->event->setResponse(new RedirectResponse($response['domainRedirection'], 301));
		} elseif ($response['urlRedirection']) {
			$this->event->setResponse(new RedirectResponse($response['urlRedirection'], 301));
		} elseif ($response['inBuild']) {
			$this->event->setResponse(new RedirectResponse($response['inBuild'], 302));
		} elseif ($response['projectInit']) {
			$this->event->setResponse(new RedirectResponse($response['projectInit'], 302));
		}

		$this->session->set('frontWebsite', $response['website']);
		$this->session->set('frontWebsiteObject', $response['websiteObject']);
	}

	/**
	 * Check admin Request
	 */
	private function adminRequest()
	{
		$websiteRequest = $this->request->get('website');
		$repository = $this->entityManager->getRepository(Website::class);
		$website = is_numeric($websiteRequest) ? $repository->find($websiteRequest) : $repository->findDefault();

		if (!$website) {
			$website = $repository->findDefault();
			if ($website) {
				$this->event->setResponse(new RedirectResponse($this->router->generate('admin_dashboard', ['website' => $website->getId()]), 302));
			}
		}

		if ($website instanceof Website) {

			$this->session->set('adminWebsite', $website);

			if (!$_FILES && !$_POST) {
				$titleService = $this->subscriber->get(TitleService::class);
				$titleService->execute($website);
			}

			$response = $this->redirectionService->inBuild($this->request, $website->getConfiguration());
			if ($response) {
				$this->event->setResponse(new RedirectResponse($response['inBuild'], 302));
			}
		}

		if (!$_FILES) {
			$this->session->set('currentEntityLocale', $this->request->get('entitylocale'));
		}
	}
}