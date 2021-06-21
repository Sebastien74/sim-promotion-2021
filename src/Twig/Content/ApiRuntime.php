<?php

namespace App\Twig\Content;

use App\Entity\Api\Api;
use App\Entity\Api\Google;
use App\Entity\Api\GoogleI18n;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * ApiRuntime
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ApiRuntime implements RuntimeExtensionInterface
{
	private $entityManager;

	/**
	 * ApiRuntime constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * Get Api i18ns
	 *
	 * @param array $website
	 * @return array
	 */
	public function apiI18ns(array $website): array
	{
		$i18ns = [];
		$apiName = NULL;
		$i18nsGetter = NULL;

		$api = $this->entityManager->getRepository(Api::class)->findByArray($website['api']['id']);
		$entities = [$api['google'], $api['facebook']];

		foreach ($entities as $entity) {

			foreach ($entity as $property => $value) {
				if(preg_match('/I18ns/', $property)) {
					$matches = explode('I18ns', $property);
					$i18nsGetter = $property;
					$apiName = $matches[0];
					break;
				}
			}

			if($i18nsGetter && $apiName) {

				$fieldNames = $this->entityManager->getClassMetadata('App\Entity\Api\\' . $apiName . 'I18n')->getFieldNames();
				$defaultLocale = $website['configuration']['locale'];

				$defaultI18n = NULL;
				foreach ($entity[$i18nsGetter] as $i18n) {
					if ($i18n['locale'] === $defaultLocale) {
						$defaultI18n = $i18n;
					}
				}

				foreach ($entity[$i18nsGetter] as $i18n) {
					foreach ($fieldNames as $fieldName) {
						$property = $i18n[$fieldName];
						if ($i18n['locale'] !== $defaultLocale) {
							$localeProperty = $property ?: $defaultI18n[$fieldName];
							$i18ns[$apiName][$i18n['locale']][$fieldName] = $localeProperty;
						} else {
							$i18ns[$apiName][$i18n['locale']][$fieldName] = $property;
						}
					}
				}
			}
		}

		return $i18ns;
	}

	/**
	 * Get Api i18n
	 *
	 * @param array $apis
	 * @param string $apiName
	 * @param string $locale
	 * @return array
	 */
	public function apiI18n(array $apis, string $apiName, string $locale): array
	{
		return !empty($apis[$apiName][$locale]) ? $apis[$apiName][$locale] : [];
	}
}