<?php

namespace App\Form\Manager\Information;

use App\Entity\Core\Website;
use App\Entity\Information\SocialNetwork;
use App\Entity\Seo\SeoConfiguration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * SocialNetworkManager
 *
 * Manage admin SocialNetwork form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SocialNetworkManager
{
    private $entityManager;

    /**
     * SocialNetworkManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Synchronize locale entities
     *
     * @param Website $website
     * @param SeoConfiguration $seoConfiguration
     */
    public function synchronizeLocales(Website $website, SeoConfiguration $seoConfiguration)
    {
        $configuration = $website->getConfiguration();
        $defaultLocale = $configuration->getLocale();
        $defaultI18n = $this->getDefaultI18n($seoConfiguration, $defaultLocale);

        if ($defaultI18n) {
            foreach ($configuration->getAllLocales() as $locale) {
                if ($locale !== $defaultLocale) {
                    $existing = $this->localeExist($seoConfiguration, $locale);
                    if (!$existing) {
                        $this->add($seoConfiguration, $locale, $defaultI18n);
                    }
                }
            }
        }
    }

    /**
     * Get default locale SocialNetwork
     *
     * @param SeoConfiguration $seoConfiguration
     * @param string $defaultLocale
     * @return SocialNetwork|null
     */
    private function getDefaultI18n(SeoConfiguration $seoConfiguration, string $defaultLocale)
    {
        $socialNetworks = $seoConfiguration->getWebsite()->getInformation()->getSocialNetworks();

        foreach ($socialNetworks as $socialNetwork) {
            if ($socialNetwork->getLocale() === $defaultLocale) {
                return $socialNetwork;
            }
        }
    }

    /**
     * Check if SocialNetwork locale exist
     *
     * @param SeoConfiguration $seoConfiguration
     * @param string $locale
     * @return bool
     */
    private function localeExist(SeoConfiguration $seoConfiguration, string $locale)
    {
        $socialNetworks = $seoConfiguration->getWebsite()->getInformation()->getSocialNetworks();

        foreach ($socialNetworks as $socialNetwork) {
            if ($socialNetwork->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add Locale SocialNetwork
     *
     * @param SeoConfiguration $seoConfiguration
     * @param string $locale
     * @param SocialNetwork $defaultI18n
     */
    private function add(SeoConfiguration $seoConfiguration, string $locale, SocialNetwork $defaultI18n)
    {
        $information = $seoConfiguration->getWebsite()->getInformation();

        $socialNetwork = new SocialNetwork();
        $socialNetwork->setLocale($locale);
        $socialNetwork->setTwitter($defaultI18n->getTwitter());
        $socialNetwork->setFacebook($defaultI18n->getFacebook());
        $socialNetwork->setGoogle($defaultI18n->getGoogle());
        $socialNetwork->setYoutube($defaultI18n->getYoutube());
        $socialNetwork->setInstagram($defaultI18n->getInstagram());
        $socialNetwork->setLinkedin($defaultI18n->getLinkedin());
        $socialNetwork->setPinterest($defaultI18n->getPinterest());
        $socialNetwork->setTripadvisor($defaultI18n->getTripadvisor());

        $information->addSocialNetwork($socialNetwork);

        $this->entityManager->persist($information);
        $this->entityManager->flush();
        $this->entityManager->refresh($information);
    }
}