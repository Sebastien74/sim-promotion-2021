<?php

namespace App\EventListener;

use App\Entity\Core\Log;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Seo\NotFoundUrl;
use App\Command\DoctrineCommand;
use App\Service\Content\RedirectionService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ExceptionListener
 *
 * Listen event Exceptions
 *
 * @property EntityManagerInterface $entityManager
 * @property DoctrineCommand $doctrineCommand
 * @property TokenStorageInterface $tokenStorage
 * @property RedirectionService $redirectionService
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property RouterInterface $router
 * @property bool $isDebug
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExceptionListener
{
    private $entityManager;
    private $doctrineCommand;
    private $tokenStorage;
    private $redirectionService;
    private $authorizationChecker;
    private $router;
    private $isDebug;

    private const IPS_DEV = ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'];

    /**
     * ExceptionListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param DoctrineCommand $doctrineCommand
     * @param TokenStorageInterface $tokenStorage
     * @param RedirectionService $redirectionService
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param bool $isDebug
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DoctrineCommand $doctrineCommand,
        TokenStorageInterface $tokenStorage,
        RedirectionService $redirectionService,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        bool $isDebug)
    {
        $this->entityManager = $entityManager;
        $this->doctrineCommand = $doctrineCommand;
        $this->tokenStorage = $tokenStorage;
        $this->redirectionService = $redirectionService;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->isDebug = $isDebug;
    }

    /**
     * onKernelException
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $allowedIP = in_array(@$_SERVER['REMOTE_ADDR'], self::IPS_DEV, true);

        if ($exception instanceof AccessDeniedHttpException) {
            $this->checkUser($request, $event);
        } elseif ($this->isDoctrineError($exception) && $allowedIP) {
            $this->doctrineCommand->update();
        } else {

            if ($exception instanceof NotFoundHttpException) {
                try {
                    $response = $this->redirectionService->execute($request);
                    if ($response['urlRedirection']) {
                        $event->setResponse(new RedirectResponse($response['urlRedirection'], 301));
                    } else {
                        $this->notFound($request);
                    }
                } catch (Exception $e) {
                }
            } else {
                try {
                    $this->logException();
                } catch (Exception $e) {
                }
            }

            if (method_exists($exception, 'getPrevious') && method_exists($exception->getPrevious(), 'getErrorCode')) {
                $errorCode = $exception->getPrevious()->getErrorCode();
                /** If schema not updated or table not existing */
                if ($allowedIP && ($errorCode === 1054 || $errorCode === 1146)) {
                    $this->doctrineCommand->update();
                    $event->setResponse(new RedirectResponse($request->getUri()));
                }
            }
        }
    }

    /**
     * To check User
     *
     * @param Request $request
     * @param ExceptionEvent $event
     */
    private function checkUser(Request $request, ExceptionEvent $event)
    {
        $inAdmin = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri()) ? 'admin' : 'front';
        $userToken = is_object($this->tokenStorage) && method_exists($this->tokenStorage, 'getToken') ? $this->tokenStorage->getToken() : NULL;
        /** @var User $user */
        $user = is_object($userToken) && method_exists($userToken, 'getUser') ? $userToken->getUser() : NULL;
        $website = $this->entityManager->getRepository(Website::class)->findOneByHost($request->getHost());

        if ($inAdmin && $this->authorizationChecker->isGranted('IS_IMPERSONATOR')) {
            $session = new Session();
            $session->getFlashBag()->add('warning', 'You have been logged out for the user ' . $user->getLogin());
            $event->setResponse(new RedirectResponse($request->getUri() . '?_switch_user=_exit'));
        } elseif ($inAdmin && $user instanceof User && $website instanceof Website) {
            $session = new Session();
            $session->getFlashBag()->add('error', 'Access denied!!');
            $event->setResponse(new RedirectResponse($this->router->generate('admin_dashboard', ['website' => $website->getId()])));
        }
    }

    /**
     * Add 404 database
     *
     * @param Request $request
     * @throws Exception
     */
    private function notFound(Request $request)
    {
        if (!preg_match('/_wdt/', $request->getUri())) {

            $website = $this->entityManager->getRepository(Website::class)->findOneByHost($request->getHost());
            $type = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri()) ? 'admin' : 'front';
            $category = $this->hasResources($request->getUri()) ? 'resource' : 'url';
            $existing = $this->entityManager->getRepository(NotFoundUrl::class)->findOneBy([
                'website' => $website,
                'url' => $request->getUri(),
                'uri' => $request->getRequestUri(),
                'type' => $type,
                'category' => $category
            ]);

            if (!$existing) {

                $newNotFound = new NotFoundUrl();
                $newNotFound->setUrl($request->getUri());
                $newNotFound->setUri($request->getRequestUri());
                $newNotFound->setType($type);
                $newNotFound->setCategory($category);
                $newNotFound->setCreatedAt(new DateTime('now'));

                if ($website) {
                    $newNotFound->setWebsite($website);
                }

                $this->entityManager->persist($newNotFound);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * Log one entry Exception log per for alert in back
     *
     * @throws Exception
     */
    private function logException()
    {
        /** @var Log $lastLog */
        $lastLog = $this->entityManager->getRepository(Log::class)->findLast();
        $currentUser = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : NULL;
        $user = !$currentUser instanceof User ? $this->entityManager->getRepository(User::class)->findOneByLogin('webmaster') : $currentUser;

        if (!$lastLog) {

            $log = new Log();
            $log->setCreatedBy($user);
            $this->entityManager->persist($log);
            $this->entityManager->flush();

        } else {

            $lastLogDate = $lastLog->getCreatedAt() instanceof DateTime ? $lastLog->getCreatedAt() : new DateTime('now');
            $currentDate = new DateTime('now');
            $diff = $lastLogDate->diff($currentDate);

            if ($diff->days > 0 && $diff->invert === 0) {
                $log = new Log();
                $log->setCreatedBy($user);
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * Check if is resource url
     *
     * @param string $uri
     * @return bool
     */
    private function hasResources(string $uri): bool
    {
        $imgExtensions = ['.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.gif', '.GIF', '.ico', '.svg'];
        $archiveExtensions = ['.zip', '.ZIP', '.rar', '.RAR', '.gz', '.GZ', '.7z', '.7Z'];
        $fileExtensions = ['.pdf', '.docx', '.xlsx', '.txt'];
        $resources = ['media\/cache', '\/build\/', '\/.git\/', '\/html\/render', 'bundles\/fosjsrouting'];
        $patterns = array_merge($imgExtensions, $archiveExtensions, $fileExtensions, $resources);

        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/', $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if is doctrine error
     *
     * @param $exception
     * @return bool
     */
    private function isDoctrineError($exception): bool
    {
        $patterns = ['Entity of type', 'SQLSTATE', 'Column not found'];

        if (method_exists($exception, 'getMessage')) {
            if (preg_match('/Disk full/', $exception->getMessage())) {
                return false;
            }
            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/', $exception->getMessage())) {
                    return true;
                }
            }
        }

        return false;
    }
}