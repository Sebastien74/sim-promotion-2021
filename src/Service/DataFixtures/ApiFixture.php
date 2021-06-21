<?php

namespace App\Service\DataFixtures;

use App\Entity\Api\Api;
use App\Entity\Api\Custom;
use App\Entity\Api\Facebook;
use App\Entity\Api\FacebookI18n;
use App\Entity\Api\Google;
use App\Entity\Api\GoogleI18n;
use App\Entity\Api\Instagram;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ApiFixture
 *
 * Api Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property array $yamlConfiguration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ApiFixture
{
    private $entityManager;
    private $yamlConfiguration = [];

    /**
     * ApiFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Api
     *
     * @param Website $website
     * @param array $yamlConfiguration
     */
    public function add(Website $website, array $yamlConfiguration)
    {
        $this->yamlConfiguration = $yamlConfiguration;
        $configuration = $website->getConfiguration();
        $allLocales = $configuration->getAllLocales();
        $defaultLocale = $configuration->getLocale();

        $api = new Api();
        $api->setWebsite($website);

        foreach ($allLocales as $locale) {
            $this->addGoogle($api, $locale, $defaultLocale);
        }

        $this->addFacebook($api, $allLocales);
        $this->addInstagram($api);
        $this->addCustom($api);

        $this->entityManager->persist($api);
    }

    /**
     * Add Google
     *
     * @param Api $api
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addGoogle(Api $api, string $locale, string $defaultLocale)
    {
        $google = $api->getGoogle();

        if (!$google instanceof Google) {
            $google = new Google();
            $google->setApi($api);
            $api->setGoogle($google);
        }

        $i18n = new GoogleI18n();
        $i18n->setLocale($locale);
        $i18n->setGoogle($google);

        $apiData = !empty($this->yamlConfiguration['apis'][$locale]['google'])
            ? $this->yamlConfiguration['apis'][$locale]['google'] : (!empty($this->yamlConfiguration['apis'][$defaultLocale]['google'])
                ? $this->yamlConfiguration['apis'][$defaultLocale]['google'] : []);

        if (!empty($apiData['ua'])) {
            $i18n->setAnalyticsUa($apiData['ua']);
        }

        if (!empty($apiData['tag_manager'])) {
            $i18n->setTagManagerKey($apiData['tag_manager']);
        }

        $this->entityManager->persist($i18n);
        $this->entityManager->persist($google);
    }

	/**
	 * Add Facebook
	 *
	 * @param Api $api
	 * @param array $allLocales
	 */
    private function addFacebook(Api $api, array $allLocales)
    {
        $facebook = new Facebook();
        $facebook->setApi($api);

        foreach ($allLocales as $locale) {
			$facebookI18n = new FacebookI18n();
			$facebookI18n->setLocale($locale);
			$facebook->addFacebookI18n($facebookI18n);
		}

        $api->setFacebook($facebook);

        $this->entityManager->persist($facebook);
    }

    /**
     * Add Instagram
     *
     * @param Api $api
     */
    private function addInstagram(Api $api)
    {
        $instagram = new Instagram();
        $instagram->setApi($api);
        $api->setInstagram($instagram);

        $this->entityManager->persist($instagram);
    }

    /**
     * Add Custom
     *
     * @param Api $api
     */
    private function addCustom(Api $api)
    {
        $custom = new Custom();
        $custom->setApi($api);
        $api->setCustom($custom);

        $this->entityManager->persist($custom);
    }
}