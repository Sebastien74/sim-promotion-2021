<?php

namespace App\Service\Core;

use App\Entity\Core\Website;
use App\Service\Content\InformationService;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use ReflectionClass;
use ReflectionException;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SwiftException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * MailerService
 *
 * To send email
 *
 * @property Swift_Mailer $mailer
 * @property Environment $twig
 * @property KernelInterface $kernel
 * @property InformationService $informationService
 * @property TranslatorInterface $translator
 * @property bool $isDebug
 * @property Request $request
 * @property Website $website
 * @property string $envName
 * @property string $transport
 * @property string $subject
 * @property string $name
 * @property string $from
 * @property array $to
 * @property string $replyTo
 * @property string $sender
 * @property string $baseTemplate
 * @property string $template
 * @property array $arguments
 * @property array $attachments
 * @property string $locale
 * @property string $host
 * @property string $schemeAndHttpHost
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MailerService
{
    private $mailer;
    private $twig;
    private $kernel;
    private $informationService;
    private $translator;
    private $isDebug;
    private $request;
    private $website;
    private $envName;
    private $transport;
    private $subject;
    private $name = "Agence Félix";
    private $from = 'support@felix-creation.fr';
    private $to = [];
    private $replyTo = 'support@felix-creation.fr';
    private $sender = 'support@felix-creation.fr';
    private $baseTemplate = 'base';
    private $template = 'core/email/email.html.twig';
    private $arguments = [];
    private $attachments = [];
    private $locale;
    private $host;
    private $schemeAndHttpHost;

    /**
     * MailerService constructor.
     *
     * @param Swift_Mailer $mailer
     * @param Environment $twig
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param InformationService $informationService
     * @param TranslatorInterface $translator
     * @param bool $isDebug
     */
    public function __construct(
        Swift_Mailer $mailer,
        Environment $twig,
        RequestStack $requestStack,
        KernelInterface $kernel,
        InformationService $informationService,
        TranslatorInterface $translator,
        bool $isDebug)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->informationService = $informationService;
        $this->translator = $translator;
        $this->isDebug = $isDebug;
        $this->request = $requestStack->getMasterRequest();
        $session = new Session();
        $this->website = $this->request && preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri())
            ? $session->get('adminWebsite')
            : $session->get('frontWebsite');
        $this->envName = $_ENV['APP_ENV_NAME'] !== 'prod' ? strtoupper($_ENV['APP_ENV_NAME']) : NULL;

        $this->setDefault();
    }

    /**
     * Send email
     */
    public function send()
    {
        if (!$this->locale) {
            $this->locale = is_object($this->request) && method_exists($this->request, 'getLocale')
                ? $this->request->getLocale() : ($this->website instanceof Website ? $this->website->getConfiguration()->getLocale() : NULL);
        }

        $this->translator->setLocale($this->locale);

        $logger = new Logger('swiftmailer');
        $errorMessage = NULL;

        try {
            $message = (new Swift_Message($this->subject));
            /** Uncomment if not working */
//            $this->setHeaders($message);
            $this->setMessage($message);
            $this->mailer->send($message);
        } catch (LoaderError $exception) {
            $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/mailer.log', 10, Logger::CRITICAL));
            $logger->critical($exception->getMessage() . ' at ' . get_class($this));
            $errorMessage = $exception->getMessage() . ' at ' . get_class($this);
        } catch (RuntimeError $exception) {
            $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/mailer.log', 10, Logger::CRITICAL));
            $logger->critical($exception->getMessage() . ' in ' . $exception->getFile() . 'at line' . $exception->getLine());
            $errorMessage = $exception->getMessage() . ' in ' . $exception->getFile() . ' line ' . $exception->getLine();
        } catch (SyntaxError $exception) {
            $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/mailer.log', 10, Logger::CRITICAL));
            $logger->critical($exception->getMessage() . ' at ' . get_class($this));
            $errorMessage = $exception->getMessage() . ' at ' . get_class($this);
        } catch (\Exception $exception) {
            $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/mailer.log', 10, Logger::CRITICAL));
            $logger->critical($exception->getMessage() . ' at ' . get_class($this));
            $errorMessage = $exception->getMessage() . ' at ' . get_class($this);
        }

        if ($this->isDebug && $errorMessage) {
            throw new HttpException(500, $errorMessage);
        }
    }

    /**
     * Set default values by Website information
     */
    private function setDefault()
    {
        $this->transport = $_ENV['SWIFT_TRANSPORT_' . strtoupper($_ENV['APP_ENV_NAME'])];
        $this->from = $this->sender = $this->replyTo = $_ENV['SWIFT_USERNAME_' . strtoupper($_ENV['APP_ENV_NAME'])];

        $information = $this->informationService->execute($this->website, $this->locale);
        $emails = is_object($information) && property_exists($information, 'emails')
            ? $information->emails : NULL;
        $hosts = is_object($information) && property_exists($information, 'hosts')
            ? $information->hosts : NULL;

        if (is_object($information) && property_exists($information, 'companyName')) {
            $this->name = $information->companyName;
        }

        if ($this->transport !== 'smtp' && is_object($emails) && property_exists($emails, 'from')) {
            $this->from = $emails->from;
            $this->sender = $emails->from;
            $this->replyTo = $emails->from;
        }

        if (is_object($emails) && property_exists($emails, 'noReply')) {
            $this->replyTo = $emails->noReply;
        }

        if (is_object($hosts) && property_exists($hosts, 'host')) {
            $this->host = $hosts->host;
        }

        if (is_object($hosts) && property_exists($hosts, 'schemeAndHttpHost')) {
            $this->schemeAndHttpHost = $hosts->schemeAndHttpHost;
        }
    }

    /**
     * Set headers
     *
     * @param Swift_Message $message
     */
    public function setHeaders(Swift_Message $message): void
    {
        $headers = $message->getHeaders();
        $messageId = $this->host ? md5($this->host) . "@" . $this->host : md5(uniqid());
        $headers->addIdHeader('Message-ID', $messageId);
        $headers->addTextHeader('MIME-Version', '1.0');
        $headers->addTextHeader('X-MailerService', 'PHP v' . phpversion());
    }

    /**
     * Set message
     *
     * @param Swift_Message $message
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setMessage(Swift_Message $message)
    {
        $this->arguments['website'] = $this->website;
        $this->arguments['base'] = $this->baseTemplate;
        $this->arguments['attachments'] = $this->attachments;
        $this->arguments['host'] = $this->host;
        $this->arguments['schemeAndHttpHost'] = $this->schemeAndHttpHost;
        $this->arguments['locale'] = $this->locale;

        $subject = $this->envName ? '[' .$this->envName. '] - ' . $this->subject : $this->subject;

        $message->setSubject($subject);
        $message->setFrom([$this->from => $this->name]);
        $message->setTo($this->to);
        $message->setReplyTo($this->replyTo);
        $message->setSender($this->sender);
        $message->setBody($this->twig->render($this->template, $this->arguments), 'text/html');
        $message->addPart(strip_tags(html_entity_decode($this->twig->render($this->template, $this->arguments))), 'text/plain');

        if (version_compare(\Swift::VERSION, '6.0.0', '>=')) {
            $message->setDate(new \DateTimeImmutable());
        } else {
            $message->setDate(new \DateTime('now'));
        }

        foreach ($this->attachments as $attachment) {
            $message->attach(Swift_Attachment::fromPath($attachment));
        }
    }

    /**
     * Logger
     *
     * @param Swift_SwiftException $exception
     * @throws ReflectionException
     */
    private function logger(Swift_SwiftException $exception)
    {
        $line = NULL;
        $logger = new Logger('swiftmailer');
        $reflexionClass = new ReflectionClass($this);

        foreach ($exception->getTrace() as $trace) {
            $trace = (object)$trace;
            if (preg_match('/' . $reflexionClass->getShortName() . '/', $trace->file) && !$line) {
                $line = $trace->line;
            }
        }

        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/mailer.log', 10, Logger::CRITICAL));
        $logger->critical($exception->getMessage() . ' at ' . get_class($this) . ' line ' . $line);
    }

    /**
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Set name
     *
     * @param null|string $name
     */
    public function setName(string $name = NULL): void
    {
        $this->name = $name ? $name : $this->name;
    }

    /**
     * Set from
     *
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        if($this->transport !== 'smtp') {
            $this->from = $from;
        }
    }

    /**
     * Set to
     *
     * @param array $to
     */
    public function setTo(array $to): void
    {
        $emails = [];
        foreach ($to as $item) {
            $matches = explode(',', $item);
            $emails = array_merge($emails, $matches);
        }

        $this->to = array_unique($emails);
    }

    public function setReplyTo(string $replyTo): void
    {
        if($this->transport !== 'smtp') {
            $this->from = $replyTo;
            $this->replyTo = $replyTo;
        }
    }

    /**
     * Set sender
     *
     * @param string $sender
     */
    public function setSender(string $sender): void
    {
        if($this->transport !== 'smtp') {
            $this->sender = $sender;
        }
    }

    /**
     * Set base template
     *
     * @param string $baseTemplate
     */
    public function setBaseTemplate(string $baseTemplate): void
    {
        $this->baseTemplate = $baseTemplate;
    }

    /**
     * Set template
     *
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * Set arguments
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Set attachments
     *
     * @param array $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * Set Website
     *
     * @param Website $website
     */
    public function setWebsite(Website $website): void
    {
        $this->website = $website;
    }

    /**
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}