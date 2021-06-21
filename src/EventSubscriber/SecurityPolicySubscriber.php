<?php

namespace App\EventSubscriber;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use App\Twig\Core\NonceRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * XSSProtector
 *
 * To manage XSS protection
 *
 * @property NonceRuntime $nonceGenerator
 * @property ContainerInterface $container
 * @property KernelInterface $kernel
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property RouterInterface $router
 * @property EntityManagerInterface $entityManager
 * @property string $uri
 * @property string $schemeAndHttpHost
 * @property string $requestUri
 * @property string $host
 * @property string $routeName
 * @property Session $session
 * @property bool $isMasterRequest
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityPolicySubscriber implements EventSubscriberInterface
{
    private $nonceGenerator;
    private $container;
    private $kernel;
    private $authorizationChecker;
    private $router;
    private $entityManager;
    private $uri;
    private $requestUri;
    private $host;
    private $schemeAndHttpHost;
    private $routeName;
    private $session;
    private $isMasterRequest;

    /**
     * XSSProtector constructor.
     *
     * @param NonceRuntime $nonceGenerator
     * @param ContainerInterface $container
     * @param KernelInterface $kernel
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        NonceRuntime $nonceGenerator,
        ContainerInterface $container,
        KernelInterface $kernel,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        EntityManagerInterface $entityManager)
    {
        $this->nonceGenerator = $nonceGenerator;
        $this->container = $container;
        $this->kernel = $kernel;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'addSecurityToResponse'];
    }

    /**
     * Adds the Content Security Policy header.
     *
     * @param ResponseEvent $event
     * @throws Exception
     */
    public function addSecurityToResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->uri = $request->getUri();
        $this->requestUri = $request->getRequestUri();
		$this->host = $request->getHost();
		$this->schemeAndHttpHost = $request->getSchemeAndHttpHost();
		$this->routeName = $request->get('_route');
		$this->session = $request->getSession();
		$this->isMasterRequest = $event->isMasterRequest();

        if (preg_match('/javascript:/', $this->requestUri)) {
            throw new \Exception('Access denied', 403);
        }

        if ($this->routeName === 'front_clear_cache') {
            $nonce = $this->session->get('app_nonce');
            if ($nonce === $request->get('token')) {
                return;
            }
        } elseif (!$this->isMasterRequest || preg_match('/_wdt/', $this->uri) || $this->routeName === 'app_new_website_project') {
            return;
        }

        $website = $this->session->get('frontWebsite');
        $tokenStorage = $this->container->get('security.token_storage');
		$response = $event->getResponse();

        $this->setCacheControl();

        if ($tokenStorage->getToken()) {

            $user = $tokenStorage->getToken()->getUser();
            $this->isSecure($event, $website, $user);

            if ($user instanceof User || $user instanceof UserFront) {
            	$userKey = $user->getSecretKey();
            	$isGrantedAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
                $_SESSION['SECURITY_USER_SECRET'] = $userKey;
                $_SESSION['SECURITY_IS_ADMIN'] = $isGrantedAdmin;
                $session = new Session();
                $session->set('SECURITY_USER_SECRET', $userKey);
                $session->set('SECURITY_IS_ADMIN', $isGrantedAdmin);
            }

            if ($user instanceof User) {
                $this->checkAdmin($user, $event);
            }
        }
//
//        if ($this->kernel->getEnvironment() !== 'dev' && $website) {
//
//            // CSP rules, using the nonce generator service
//            // Source for others : https://blobfolio.com/2018/01/on-content-security-policy-headers
//
//            $protocol = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])];
//            $nonce = $this->nonceGenerator->getNonce();
//
////            # The default is the current site, and any protocol (SSL or not) connection to domain.com (including subdomains).
////            $cspHeader = "default-src 'self' " . $protocol . "://" . $host . " " . $protocol . "://*." . $host . ";";
////            # For scripts, maybe we want to whitelist Google Analytics, the Apocalypse Meow "noopener" script, and allow eval() so Vue.JS doesn't explode.
////            $cspHeader .= "script-src 'self' " . $protocol . "://" . $host . " " . $protocol . "://*." . $host . " https://www.google-analytics.com 'nonce-" . $nonce . "' 'unsafe-eval';";
////            # Images also might need Analytics, but also Gravatar and base64-encoded data-URI streams.
////            $cspHeader .= "img-src 'self' " . $protocol . "://" . $host . " " . $protocol . "://*." . $host . " https://www.google-analytics.com https://*.gravatar.com data:;";
////            # Maybe inline styles aren't really a concern. Most CSS-related exploits work best against browsers that don't know CSP anyway.
////            $cspHeader .= "style-src 'self' " . $protocol . "://" . $host . " " . $protocol . "://*." . $host . " 'unsafe-inline';";
////            # Objects? No. This isn't 1995.
////            $cspHeader .= "object-src 'none';";
////
////            $cspHeader = "script-src 'nonce-" . $nonce . "' 'self' 'unsafe-inline' 'unsafe-eval' 'strict-dynamic' https: http:; object-src 'none';";
////            $response->headers->set("Content-Security-Policy", [$cspHeader]);
//
//            $response->headers->set('Access-Control-Allow-Origin', $this->schemeAndHttpHost);
//        }
//
//        $response->headers->set("X-Frame-Options", 'deny');
//        $response->headers->set("Strict-Transport-Security", 'max-age=31536000; includeSubDomains; preload');
    }

    /**
     * Set Cache Control
     */
    private function setCacheControl()
    {
        if (preg_match('/\/js\/routing/', $this->uri)) {
            header("Cache-Control: no-cache, must-revalidate");
            /** HTTP 1.1 */
            header("Pragma: no-cache");
            /** HTTP 1.0 */
            header("Cache-Control: max-age=2592000");
            /** 30days (60sec * 60min * 24hours * 30days) */
        }
    }

	/**
	 * Check if is secure website & redirect if User isn't connected
	 *
	 * @param ResponseEvent $responseEvent
	 * @param mixed|null $website
	 * @param User|string|null $user
	 */
    private function isSecure(ResponseEvent $responseEvent, $website = NULL, $user = NULL)
    {
        $allowedRoutes = [
            'security_front_login',
            'security_front_password_request',
            'security_front_password_confirm',
            'security_front_register',
            'front_webmaster_toolbox'
        ];

        if (!$this->isMasterRequest
            || preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->uri)
            || preg_match('/\/secure\/user/', $this->uri)
            || preg_match('/\/front\//', $this->uri)
            || in_array($this->routeName, $allowedRoutes)
            || $user instanceof UserFront) {
            return;
        }

        $website = is_array($website) ? $website : $this->entityManager->getRepository(Website::class)->findOneByHost($this->host, false, true);
        if ($website['security']['secureWebsite'] && !$user instanceof User) {
            $responseEvent->setResponse(new RedirectResponse($this->router->generate('security_front_login')));
        }
    }

    /**
     * Check if User is allowed to edit Website
     *
     * @param User $user
     * @param ResponseEvent $responseEvent
     */
    private function checkAdmin(User $user, ResponseEvent $responseEvent)
    {
        if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->uri)
            && !preg_match('/_switch_user/', $this->requestUri)) {

            $website = $this->session->get('adminWebsite');

            if ($website instanceof Website) {

            	$websiteId = $website->getId();
                $allowed = false;
                foreach ($user->getWebsites() as $userWebsite) {
                    if ($userWebsite->getId() === $websiteId) {
                        $allowed = true;
                        break;
                    }
                }

                if (!$allowed && !in_array('ROLE_INTERNAL', $user->getRoles())) {
                    if (count($user->getWebsites()) > 0) {
                        $responseEvent->setResponse(new RedirectResponse($this->router->generate('admin_dashboard', ['website' => $user->getWebsites()[0]->getId()])));
                    } else {
                        header('Location: ' . $this->schemeAndHttpHost . '/denied.php?site=true');
                        exit();
                    }
                }
            }
        }
    }
}