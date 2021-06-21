<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Configuration;
use App\Service\Translation\Extractor;
use Exception;

/**
 * TranslationsFixture
 *
 * Translations Fixtures management
 *
 * @property Extractor $extractor
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationsFixture
{
    private $extractor;

    /**
     * TranslationsFixture constructor.
     *
     * @param Extractor $extractor
     */
    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * Generate translations
     *
     * @param Configuration $configuration
     * @param array $websites
     * @throws Exception
     */
    public function generate(Configuration $configuration, array $websites)
    {
        $allLocales = $configuration->getAllLocales();

        if (count($websites) === 0) {
            foreach ($allLocales as $locale) {
                $this->extractor->extract($locale);
            }
            $yamlTranslations = $this->extractor->findYaml($allLocales);
            foreach ($yamlTranslations as $domain => $localeTranslations) {
                foreach ($localeTranslations as $locale => $translations) {
                    foreach ($translations as $keyName => $content) {
                        $this->extractor->generateTranslation($configuration->getLocale(), $locale, $domain, $content, $keyName);
                    }
                }
            }
            $this->extractor->initFiles($allLocales);
        }
    }
}