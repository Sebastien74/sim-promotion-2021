<?php

namespace App\Form\Manager\Api;

use App\Entity\Api\Facebook;
use App\Entity\Api\FacebookI18n;
use App\Entity\Core\Website;
use App\Entity\Seo\SeoConfiguration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * FacebookManager
 *
 * Manage admin FacebookManager form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookManager
{
	private $entityManager;

	/**
	 * FacebookManager constructor.
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
		$facebook = $seoConfiguration->getWebsite()->getApi()->getFacebook();
		$defaultI18n = $this->getDefaultI18n($facebook, $defaultLocale);

		if ($defaultI18n) {
			foreach ($configuration->getAllLocales() as $locale) {
				if ($locale !== $defaultLocale) {
					$existing = $this->localeExist($facebook, $locale);
					if (!$existing) {
						$this->add($facebook, $locale, $defaultI18n);
					}
				}
			}
		}
	}

	/**
	 * Get default locale FacebookI18n
	 *
	 * @param Facebook $facebook
	 * @param string $defaultLocale
	 * @return FacebookI18n|null
	 */
	private function getDefaultI18n(Facebook $facebook, string $defaultLocale): ?FacebookI18n
	{
		foreach ($facebook->getFacebookI18ns() as $i18n) {
			if ($i18n->getLocale() === $defaultLocale) {
				return $i18n;
			}
		}

		$this->add($facebook, $defaultLocale);
	}

	/**
	 * Check if FacebookI18ns locale exist
	 *
	 * @param Facebook $facebook
	 * @param string $locale
	 * @return bool
	 */
	private function localeExist(Facebook $facebook, string $locale): bool
	{
		foreach ($facebook->getFacebookI18ns() as $facebookI18n) {
			if ($facebookI18n->getLocale() === $locale) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add FacebookI18n
	 *
	 * @param Facebook $facebook
	 * @param string $locale
	 * @param FacebookI18n|null $defaultFacebookI18n
	 * @return FacebookI18n
	 */
	private function add(Facebook $facebook, string $locale, FacebookI18n $defaultFacebookI18n = NULL): FacebookI18n
	{
		$facebookI18n = new FacebookI18n();
		$facebookI18n->setLocale($locale);
		$facebookI18n->setFacebook($facebook);

		$facebook->addFacebookI18n($facebookI18n);

		$this->entityManager->persist($facebook);
		$this->entityManager->flush();

		return $facebookI18n;
	}
}