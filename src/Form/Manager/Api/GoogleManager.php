<?php

namespace App\Form\Manager\Api;

use App\Entity\Api\Google;
use App\Entity\Api\GoogleI18n;
use App\Entity\Core\Website;
use App\Entity\Seo\SeoConfiguration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * GoogleManager
 *
 * Manage admin GoogleManager form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GoogleManager
{
    private $entityManager;

    /**
     * GoogleManager constructor.
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
        $google = $seoConfiguration->getWebsite()->getApi()->getGoogle();
        $defaultI18n = $this->getDefaultI18n($google, $defaultLocale);

        if ($defaultI18n) {
            foreach ($configuration->getAllLocales() as $locale) {
                if ($locale !== $defaultLocale) {
                    $existing = $this->localeExist($google, $locale);
                    if (!$existing) {
                        $this->add($google, $locale, $defaultI18n);
                    }
                }
            }
        }
    }

    /**
     * Get default locale GoogleI18n
     *
     * @param Google $google
     * @param string $defaultLocale
     * @return GoogleI18n|null
     */
    private function getDefaultI18n(Google $google, string $defaultLocale)
    {
        foreach ($google->getGoogleI18ns() as $i18n) {
            if ($i18n->getLocale() === $defaultLocale) {
                return $i18n;
            }
        }

        $this->add($google, $defaultLocale);
    }

    /**
     * Check if GoogleI18n locale exist
     *
     * @param Google $google
     * @param string $locale
     * @return bool
     */
    private function localeExist(Google $google, string $locale)
    {
        foreach ($google->getGoogleI18ns() as $googleI18n) {
            if ($googleI18n->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add GoogleI18n
     *
     * @param Google $google
     * @param string $locale
     * @param GoogleI18n|NULL $defaultGoogleI18n
     */
    private function add(Google $google, string $locale, GoogleI18n $defaultGoogleI18n = NULL)
    {
        $googleI18n = new GoogleI18n();
        $googleI18n->setLocale($locale);
        $googleI18n->setGoogle($google);

        if ($defaultGoogleI18n) {
            $googleI18n->setClientId($defaultGoogleI18n->getClientId());
            $googleI18n->setAnalyticsUa($defaultGoogleI18n->getAnalyticsUa());
            $googleI18n->setAnalyticsAccountId($defaultGoogleI18n->getAnalyticsAccountId());
            $googleI18n->setAnalyticsStatsDuration($defaultGoogleI18n->getAnalyticsStatsDuration());
            $googleI18n->setTagManagerKey($defaultGoogleI18n->getTagManagerKey());
            $googleI18n->setTagManagerLayer($defaultGoogleI18n->getTagManagerLayer());
            $googleI18n->setSearchConsoleKey($defaultGoogleI18n->getSearchConsoleKey());
            $googleI18n->setMapKey($defaultGoogleI18n->getMapKey());
            $googleI18n->setPlaceId($defaultGoogleI18n->getPlaceId());
        }

        $google->addGoogleI18n($googleI18n);
        $this->entityManager->persist($google);
        $this->entityManager->flush();
    }
}