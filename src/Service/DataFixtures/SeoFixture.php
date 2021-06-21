<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Seo\SeoConfiguration;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;

/**
 * SeoFixture
 *
 * Seo Fixture management
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoFixture
{
    private $entityManager;

    /**
     * SeoFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Seo
     *
     * @param Website $website
     * @param User|null $user
     */
    public function add(Website $website, User $user = NULL)
    {
        $configuration = $website->getConfiguration();
        $seoConfiguration = $this->addConfiguration($website, $user);
        $allLocales = $configuration->getAllLocales();

        foreach ($allLocales as $locale) {
            $this->addI18n($seoConfiguration, $locale, $user);
        }

        $this->entityManager->persist($website);
    }

    /**
     * Add Seo Configuration
     *
     * @param Website $website
     * @param User|null $user
     * @return SeoConfiguration
     */
    private function addConfiguration(Website $website, User $user = NULL): SeoConfiguration
    {
        $configuration = new SeoConfiguration();
        $configuration->setWebsite($website);

        if ($user) {
            $configuration->setCreatedBy($user);
        }

        $website->setSeoConfiguration($configuration);

        return $configuration;
    }

    /**
     * Add Seo Configuration
     *
     * @param SeoConfiguration $configuration
     * @param string $locale
     * @param User|null $user
     */
    private function addI18n(SeoConfiguration $configuration, string $locale, User $user = NULL)
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setAuthorType('Organization');
        $i18n->setPlaceholder('Organization');
        $i18n->setWebsite($configuration->getWebsite());

        if ($user) {
            $i18n->setCreatedBy($user);
        }

        $configuration->addI18n($i18n);
    }
}