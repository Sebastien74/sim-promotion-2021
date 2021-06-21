<?php

namespace App\Controller;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Security\User;
use App\Entity\Seo\Seo;
use App\Entity\Seo\Url;
use App\Service\Content\MenuService;
use App\Service\Content\SeoService;
use App\Command\JsRoutingCommand;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ExceptionController
 *
 * Manage render Exceptions
 *
 * @property int $statusCode
 * @property bool $isDebug
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property KernelInterface $kernel
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExceptionController extends BaseController
{
    protected $statusCode = 0;
    protected $isDebug = false;
    protected $entityManager;
    protected $request;
    protected $kernel;
    protected $website;

    /**
     * Page render
     *
     * @param Request $request
     * @param Exception|FlattenException $exception
     * @param DebugLoggerInterface|null $logger
     * @return Response
     * @throws Exception
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null): Response
	{
        if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri()) && !$this->getUser() instanceof User) {
            return $this->redirect($this->request->getSchemeAndHttpHost());
        }

        $this->isDebug = $this->kernel->isDebug();
        $this->statusCode = $exception->getStatusCode();
        $this->statusCode = $this->statusCode === 0 ? 500 : $this->statusCode;

        $arguments = $this->setArguments($request, $exception, $logger);
        $template = $this->getTemplate();

        return $this->render($template, $arguments);
    }

    /**
     * Log javaScript errors
     *
     * @Route("/core/dev/logger/javascript/erros", methods={"GET"}, name="javascript_errors_logger", options={"expose"=true}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param MailerInterface $mailer
     * @return JsonResponse
     */
    public function jsErrorsLogger(Request $request, MailerInterface $mailer): JsonResponse
	{
        $routesErrors = 0;

        $logger = new Logger('javascript-errors');
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/javascript-errors.log', 20, Logger::INFO));
        $logger->critical('Stacktrace start.');

        foreach ($request->query->all() as $parameter => $value) {
            $value = urldecode($value);
            $logger->critical($parameter . ': ' . $value);
            if (preg_match('/route/', $value) && preg_match('/does not exist/', $value) || preg_match('/fosjsrouting/', $value)) {
                $routesErrors++;
            }
        }

        $logger->critical('Stacktrace end.');

        if ($this->kernel->getEnvironment() !== 'dev') {

            try {
                $email = (new NotificationEmail())->from('dev@felix-creation.fr')
                    ->to('sebastien@felix-creation.fr')
                    ->subject('Javascript error')
                    ->markdown("Une erreur javaScript est survenue sur " . $request->getHost())
                    ->action("Plus d'information ?", $request->getHost())
                    ->importance(NotificationEmail::IMPORTANCE_HIGH);
                $mailer->send($email);
            } catch (Exception | TransportExceptionInterface $exception) {
            }
		}

        if ($routesErrors >= 2) {
            $this->subscriber->get(JsRoutingCommand::class)->dump();
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Get template
     *
     * @return string
     */
    private function getTemplate(): string
	{
        $filesystem = new Filesystem();
        $dirname = $this->kernel->getProjectDir() . '\templates\bundles\TwigBundle\Exception\\';
        $isNotFound = $this->statusCode === 404;
        $allowedPS = $this->website instanceof Website ? $this->website->getConfiguration()->getIpsDev() : [];

        if ($this->isDebug && !$isNotFound
            || preg_match('/.local/', $this->request->getSchemeAndHttpHost()) && !$isNotFound
            || in_array(@$_SERVER['REMOTE_ADDR'], $allowedPS) && !$isNotFound) {
            return '@Twig/Exception/stack-traces.html.twig';
        } elseif ($filesystem->exists($dirname . 'exception_full.html.twig')) {
            return '@Twig/Exception/exception_full.html.twig';
        } elseif ($filesystem->exists($dirname . 'error-' . $this->statusCode . '.html.twig')) {
            return '@Twig/Exception/error-' . $this->statusCode . '.html.twig';
        }

        return '@Twig/Exception/error.html.twig';
    }

    /**
     * Set page arguments
     *
     * @param Request $request
     * @param Exception|FlattenException $exception
     * @param DebugLoggerInterface|null $logger
     * @return array
     */
    private function setArguments(Request $request, $exception, DebugLoggerInterface $logger = null): array
	{
        $arguments['is_debug'] = $this->isDebug;
        $arguments['logger'] = $logger;
        $arguments['status_code'] = $this->statusCode;
        $arguments['status_text'] = $exception->getMessage();
        $arguments['exception'] = $exception;
        $arguments['currentContent'] = NULL;

        if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri())) {

            /** @var Website $website */
            $websiteSession = $request->getSession()->get('adminWebsite');

            if (!$websiteSession && !$this->request->get('website')) {
                $website = $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
            } else {
                $website = $websiteSession ?: $this->entityManager->getRepository(Website::class)->find($this->request->get('website'));
            }

            $arguments['website'] = $this->website = $website;
            $arguments['configuration'] = $website->getConfiguration();
            $arguments['template'] = 'admin';

        } else {

            $website = $this->getWebsite($request);
            $configuration = $website instanceof Website ? $website->getConfiguration() : NULL;
			$userBackIPS = $configuration ? $configuration->getAllIPS() : [];

			$arguments['isUserBack'] = in_array(@$_SERVER['REMOTE_ADDR'], $userBackIPS) || $this->getUser() instanceof User;
            $arguments['website'] = $this->website = $website;
            $arguments['seo'] = $website ? $this->getSeo($website, $request) : NULL;
            $arguments['template'] = $website ? $configuration->getTemplate() : NULL;
            $arguments['templateName'] = 'error';
            $arguments['mainMenus'] = $website ? $this->subscriber->get(MenuService::class)->getAll($website) : [];
        }

        return $arguments;
    }

	/**
	 * Get SEO
	 *
	 * @param Website $website
	 * @param Request $request
	 * @return array
	 */
    private function getSeo(Website $website, Request $request): ?array
	{
        $defaultExceptionUrl = NULL;
        $currentLocaleExisting = false;
        $locales = $website->getConfiguration()->getAllLocales();
        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
            'website' => $website,
            'slug' => 'error'
        ]);

        $exceptionUrl = NULL;

        foreach ($locales as $locale) {

            $existingUrl = false;

            if ($page) {
                foreach ($page->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url->getLocale() === $locale) {
                        $existingUrl = true;
                        $currentLocaleExisting = true;
                    }
                    if ($url->getLocale() === $request->getLocale()) {
                        $exceptionUrl = $url;
                    }
                }
            }

            if (!$existingUrl && $page || null === $exceptionUrl && $page) {

                /** @var User $createdBy */
                $createdBy = $this->entityManager->getRepository(User::class)->findOneBy(['login' => 'webmaster']);

                $errorUrl = new Url();
                $errorUrl->setLocale($locale);
                $errorUrl->setCode('error');
                $errorUrl->setWebsite($website);
                $errorUrl->setHideInSitemap(true);
                $errorUrl->setIsIndex(false);
                $errorUrl->setCreatedBy($createdBy);

                $seo = new Seo();
                $seo->setUrl($errorUrl);
                $seo->setCreatedBy($createdBy);
                $errorUrl->setSeo($seo);

                $page->addUrl($errorUrl);

                $this->entityManager->persist($page);
                $this->entityManager->flush();

                if ($locale === $request->getLocale()) {
                    $exceptionUrl = $errorUrl;
                    $currentLocaleExisting = true;
                }

                if ($locale === $website->getConfiguration()->getLocale()) {
                    $defaultExceptionUrl = $errorUrl;
                }
            }
        }

        if (!$currentLocaleExisting) {
            $exceptionUrl = $defaultExceptionUrl ? $defaultExceptionUrl : NULL;
            $locale = $exceptionUrl ? $exceptionUrl->getLocale() : $website->getConfiguration()->getLocale();
            $request->setLocale($locale);
        }

        return $page ? $this->subscriber->get(SeoService::class)->execute($exceptionUrl, $page) : NULL;
    }
}