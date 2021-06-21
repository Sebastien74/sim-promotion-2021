<?php

namespace App\Service\Security;

use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use App\Service\Core\CronSchedulerService;
use App\Service\Core\MailerService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PasswordExpire
 *
 * Check if users passwords expire and send email
 *
 * @property EntityManagerInterface $entityManager
 * @property MailerService $mailer
 * @property TranslatorInterface $translator
 * @property CronSchedulerService $cronSchedulerService
 * @property string $logPath
 * @property int $adminDelay
 * @property InputInterface $input
 * @property array $users
 * @property array $emails
 * @property array $emailNames
 * @property array $hosts
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PasswordExpire
{
    private $entityManager;
    private $mailer;
    private $translator;
    private $cronSchedulerService;
    private $adminDelay = 365;
    private $users = [];
    private $emails = [];
    private $emailNames = [];
    private $hosts = [];

    /**
     * PasswordExpire constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MailerService $mailer
     * @param TranslatorInterface $translator
     * @param CronSchedulerService $cronSchedulerService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MailerService $mailer,
        TranslatorInterface $translator,
        CronSchedulerService $cronSchedulerService)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->cronSchedulerService = $cronSchedulerService;
    }

    /**
     * To execute service
     *
     * @param InputInterface|null $input
     * @param string|null $command
     * @throws Exception
     */
    public function execute(InputInterface $input = NULL, string $command = NULL)
    {
        $websiteRepository = $this->entityManager->getRepository(Website::class);
        $websites = $websiteRepository->findAll();
        $website = $websites[0];
        $this->adminDelay = $website->getSecurity()->getAdminPasswordDelay();

        /** Users Back */
        $this->parseUsers(User::class, 15, 'password-info');
        $this->parseUsers(User::class, 0, 'password-alert');
        $this->cronSchedulerService->logger('[OK] ' . User::class . ' successfully executed', $input);

        /** Users Front */
        $this->parseUsers(UserFront::class, 15, 'password-info');
        $this->parseUsers(UserFront::class, 0, 'password-alert');
        $this->cronSchedulerService->logger('[OK] ' . UserFront::class . ' successfully executed', $input);

        $this->getEmails($websites);
        $this->sendEmails();
        $this->cronSchedulerService->logger('[OK] Email successfully sent.', $input);

        $this->cronSchedulerService->logger('[EXECUTED] ' . $command, $input);
    }

    /**
     * Get Users
     *
     * @param string $classname
     * @param int $delta
     * @param string $alert
     * @throws Exception
     */
    private function parseUsers(string $classname, int $delta, string $alert)
    {
        $users = $this->entityManager->getRepository($classname)->findAll();

        foreach ($users as $user) {

            /** @var User|UserFront $user */

            $findDate = $this->getDateTime($user, $delta);

            if ($user->getResetPasswordDate() < $findDate && $user->getActive()) {

                if (is_array($user->getAlerts()) && !in_array($alert, $user->getAlerts())) {

                    $this->setUser($user, $alert);

                    $userType = $user instanceof User ? 'back' : 'front';
                    $this->users[$userType][$alert][$user->getId()] = $user;

                    if ($alert === 'password-alert' && !empty($this->users[$userType]['password-info'][$user->getId()])) {
                        unset($this->users[$userType]['password-info'][$user->getId()]);
                    }
                }
            }
        }
    }

    /**
     * Get DateTime
     *
     * @param $user
     * @param int $delta
     * @return DateTimeImmutable
     * @throws Exception
     */
    private function getDateTime($user, int $delta)
    {
        $userDelay = $user instanceof UserFront ? $user->getWebsite()->getSecurity()->getFrontPasswordDelay() : $this->adminDelay;
        $delay = $userDelay - $delta;

        $date = new \DateTime('now');
        $findDate = new DateTimeImmutable($date->format('Y-m-d H:i:s'));
        return $findDate->modify('-' . $delay . ' days');
    }

    /**
     * Set User
     *
     * @param $user
     * @param string $alert
     */
    private function setUser($user, string $alert)
    {
        /** @var UserFront|User $user */

        $alerts = $user->getAlerts();
        $alerts[] = $alert;
        $user->setAlerts(array_unique($alerts));
        $user->setResetPassword(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Get Websites senders emails
     *
     * @param mixed $websites
     */
    private function getEmails($websites): void
    {
        foreach ($websites as $website) {
            /** @var Website $website */
            foreach ($website->getInformation()->getEmails() as $email) {
                if ($email->getSlug() === 'support' || $email->getSlug() === 'no-reply') {
                    $this->emails[$website->getId()][$email->getLocale()][$email->getSlug()] = $email->getEmail();
                }
            }
        }
    }

    /**
     * Send emails alerts
     */
    private function sendEmails()
    {
        foreach ($this->users as $userType => $alerts) {
            foreach ($alerts as $alertType => $users) {
                foreach ($users as $user) {

                    /** @var User|UserFront $user */

                    $website = $this->getUserWebsite($user);
                    $defaultLocale = $website->getConfiguration()->getLocale();
                    $from = $this->getEmailBySlug($website, $user, 'support', $defaultLocale);
                    $reply = $this->getEmailBySlug($website, $user, 'no-reply', $defaultLocale);

                    $this->mailer->setSubject($this->getEmailSubject($alertType, $user));
                    $this->mailer->setTo([$user->getEmail()]);
                    $this->mailer->setName($this->getMailName($website, $user));
                    $this->mailer->setFrom($from);
                    $this->mailer->setReplyTo($reply);
                    $this->mailer->setTemplate('front/default/actions/security/email/password-expire.html.twig');
                    $this->mailer->setArguments(['expire' => $alertType !== 'password-info', 'user' => $user, 'website' => $website, 'schemeAndHttpHost' => $this->getSchemeAndHttpHost($website)]);
                    $this->mailer->setLocale($user->getLocale());
                    $this->mailer->send();
                }
            }
        }
    }

    /**
     * Get Email subject
     *
     * @param string $alertType
     * @param User|UserFront $user
     * @return string
     */
    private function getEmailSubject(string $alertType, $user)
    {
        return $alertType === 'password-info'
            ? $this->translator->trans('Votre mot de passe arrive à expiration', [], 'security_cms', $user->getLocale())
            : $this->translator->trans('Votre mot de passe à expiré', [], 'security_cms', $user->getLocale());
    }

    /**
     * Get Email by slug
     *
     * @param Website $website
     * @param User|UserFront $user
     * @param string $slug
     * @param string $defaultLocale
     * @return string
     */
    private function getEmailBySlug(Website $website, $user, string $slug, string $defaultLocale)
    {
        return !empty($this->emails[$website->getId()][$user->getLocale()][$slug])
            ? $this->emails[$website->getId()][$user->getLocale()][$slug]
            : $this->emails[$website->getId()][$defaultLocale][$slug];
    }

    /**
     * Get User Website
     *
     * @param User|UserFront $user
     * @return Website
     */
    private function getUserWebsite($user)
    {
        $website = $user instanceof UserFront ? $user->getWebsite() : $user->getWebsites()[0];

        if (!$website) {
            $websites = $this->entityManager->getRepository(Website::class)->findAll();
            foreach ($websites as $websiteConfiguration) {
                if ($websiteConfiguration->getConfiguration()->getDomains()->count() > 0) {
                    $website = $websiteConfiguration;
                    break;
                }
            }
        }

        return $website;
    }

    /**
     * Get User Website
     *
     * @param Website $website
     * @param User|UserFront $user
     * @return string
     */
    private function getMailName(Website $website, $user)
    {
        if (!empty($this->emailNames[$website->getId()])) {
            return $this->emailNames[$website->getId()];
        }

        $defaultLocale = $website->getConfiguration()->getLocale();
        $defaultLocaleName = NULL;

        foreach ($website->getInformation()->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $user->getLocale()) {
                $this->emailNames[$website->getId()] = $i18n->getTitle();
            }
            if (empty($this->emailNames[$website->getId()]) && $i18n->getLocale() === $defaultLocale) {
                $this->emailNames[$website->getId()] = $i18n->getTitle();
            }
        }

        if (empty($this->emailNames[$website->getId()])) {
            $this->emailNames[$website->getId()] = 'Agence Félix';
        }

        return $this->emailNames[$website->getId()];
    }

    /**
     * Get Website schemeAndHttpHost
     *
     * @param Website $website
     * @return string|null
     */
    private function getSchemeAndHttpHost(Website $website)
    {
        if (!empty($this->hosts[$website->getId()])) {
            return $this->hosts[$website->getId()];
        }

        $configuration = $website->getConfiguration();
        $domains = $this->entityManager->getRepository(Domain::class)->findBy([
            'configuration' => $configuration,
            'hasDefault' => true
        ]);

        $protocol = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])] . '://';
        $domain = $domains ? $protocol . $domains[0]->getName() : NULL;
        $this->hosts[$website->getId()] = $domain;

        return $domain;
    }
}