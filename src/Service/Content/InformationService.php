<?php

namespace App\Service\Content;

use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * InformationService
 *
 * Information management
 *
 * @property WebsiteRepository $websiteRepository
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property string $locale
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationService
{
    private $websiteRepository;
    private $request;
    private $entityManager;
    private $locale;

    /**
     * InformationService constructor.
     *
     * @param WebsiteRepository $websiteRepository
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(WebsiteRepository $websiteRepository, RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->websiteRepository = $websiteRepository;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
    }

    /**
     * Execute service
     *
     * @param Website|null $website
     * @param string|null $locale
     * @return object
     */
    public function execute(Website $website = NULL, string $locale = NULL)
    {
        try {

            /** @var Website $website */
            $website = $website instanceof Website ? $website
                : ($this->request ? $this->websiteRepository->findOneByHost($this->request->getHost()) : $this->websiteRepository->findDefault());
            $this->locale = $locale ? $locale : (is_object($this->request) && method_exists($this->request, 'getLocale')
                ? $this->request->getLocale() : $website->getConfiguration()->getLocale());
            $information = $website->getInformation();
            $configuration = $website->getConfiguration();

            return (object)[
                'companyName' => $this->getCompanyName($information, $configuration),
                'emails' => $this->getEmails($information, $configuration),
                'hosts' => $this->getHost($website)
            ];
        } catch (NonUniqueResultException $e) {
        }
    }

    /**
     * Get i18n company name
     *
     * @param Information $information
     * @param Configuration $configuration
     * @return string
     */
    private function getCompanyName(Information $information, Configuration $configuration)
    {
        $default = 'Agence Félix';
        $current = NULL;

        foreach ($information->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $this->locale) {
                $current = $i18n->getTitle();
            } elseif ($i18n->getLocale() === $configuration->getLocale()) {
                $default = $i18n->getTitle();
            }
        }

        return $current ? $current : $default;
    }

    /**
     * Get i18n Email[]
     *
     * @param Information $information
     * @param Configuration $configuration
     * @return string
     */
    private function getEmails(Information $information, Configuration $configuration)
    {
        $emails = [];
        $from = 'dev@felix-creation.fr';
        $noReply = 'noreply@felix-creation.fr';

        foreach ($information->getEmails() as $email) {

            $emails[$email->getLocale()][] = $email;

            if ($email->getLocale() === $this->locale && $email->getSlug() === 'support') {
                $from = $email->getEmail();
            }

            if ($email->getLocale() === $configuration->getLocale() && $email->getSlug() === 'support' && $from === 'dev@felix-creation.fr') {
                $from = $email->getEmail();
            }

            if ($email->getLocale() === $this->locale && $email->getSlug() === 'no-reply') {
                $noReply = $email->getEmail();
            }

            if ($email->getLocale() === $configuration->getLocale() && $email->getSlug() === 'no-reply' && $from === 'noreply@felix-creation.fr') {
                $noReply = $email->getEmail();
            }
        }

        return (object)[
            'all' => $emails,
            'from' => $from,
            'noReply' => $noReply
        ];
    }

    /**
     * Get Hosts
     *
     * @param Website|null $website
     * @return object
     */
    private function getHost(Website $website = NULL)
    {
        $host = NULL;
        $schemeAndHttpHost = NULL;

        if (is_object($this->request) && method_exists($this->request, 'getHost')) {
            $host = $this->request->getHost();
            $schemeAndHttpHost = $this->request->getSchemeAndHttpHost();
        } elseif ($website instanceof Website) {
            $configuration = $website->getConfiguration();
            $defaultLocale = $configuration->getLocale();
            $domains = $this->entityManager->getRepository(Domain::class)->findBy(['configuration' => $configuration, 'locale' => $defaultLocale, 'hasDefault' => true]);
            $host = !empty($domains) ? $domains[0]->getName() : NULL;
            $schemeAndHttpHost = 'https://' . $host;
        }

        return (object)[
            'host' => $host,
            'schemeAndHttpHost' => $schemeAndHttpHost
        ];
    }
}