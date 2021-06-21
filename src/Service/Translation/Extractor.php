<?php

namespace App\Service\Translation;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationUnit;
use App\Helper\Core\InterfaceHelper;
use App\Repository\Translation\TranslationDomainRepository;
use App\Repository\Translation\TranslationRepository;
use App\Repository\Translation\TranslationUnitRepository;
use App\Service\Development\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Extractor
 *
 * To extract translations
 *
 * @property KernelInterface $kernel
 * @property EntityManagerInterface $entityManager
 * @property EntityService $entityService
 * @property InterfaceHelper $interfaceHelper
 * @property TranslatorInterface $translator
 * @property TranslationDomainRepository $domainRepository
 * @property TranslationUnitRepository $unitRepository
 * @property TranslationRepository $translationRepository
 * @property array $interfaceNames
 * @property array $entityFields
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Extractor
{
    private $kernel;
    private $entityManager;
    private $entityService;
    private $interfaceHelper;
    private $translator;
    private $domainRepository;
    private $unitRepository;
    private $translationRepository;
    private $interfaceNames = [];
    private $entityFields = ['plural', 'singular', 'add'];

    /**
     * Extractor constructor.
     *
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $entityManager
     * @param EntityService $entityService
     * @param InterfaceHelper $interfaceHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        EntityService $entityService,
        InterfaceHelper $interfaceHelper,
        TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $this->entityService = $entityService;
        $this->interfaceHelper = $interfaceHelper;
        $this->translator = $translator;
        $this->domainRepository = $this->entityManager->getRepository(TranslationDomain::class);
        $this->unitRepository = $this->entityManager->getRepository(TranslationUnit::class);
        $this->translationRepository = $this->entityManager->getRepository(Translation::class);

        $this->setInterfaceNames();
    }

    /**
     * Extract default locale translations
     *
     * @param string $locale
     * @throws Exception
     */
    public function extract(string $locale)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'translation:update',
            'locale' => $locale,
            '--output-format' => 'yaml',
            '--force' => true
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * Get yaml files
     *
     * @param array $locales
     * @return array
     */
    public function findYaml(array $locales): array
    {
        $translations = [];
        $finder = new Finder();
        $finder->files()->in($this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'translations');

        foreach ($finder as $file) {
            $matches = explode('.', $file->getFilename());
            $domain = str_replace('+intl-icu', '', $matches[0]);
            $locale = $matches[1];
            if ($domain && $file->getFilename() !== '.gitkeep' && in_array($locale, $locales) && preg_match('/.yaml/', $file->getPathname())) {
                $translations[$domain][$locale] = Yaml::parseFile($file->getPathname());
            }
        }

        return $translations;
    }

    /**
     * Set Domain
     *
     * @param string $domainName
     * @return TranslationDomain
     */
    private function setDomain(string $domainName): TranslationDomain
    {
        $domain = $this->domainRepository->findOneByName($domainName);
        $entityName = str_replace(['entity_', '+intl-icu'], ['', ''], $domainName);
        $toExtract = ['gdpr', 'build', 'ie_alert', 'email', 'exception', 'messages', 'security', 'security_cms'];

        $adminName = $domainName;
        if (preg_match('/entity_/', $domainName) && !empty($this->interfaceNames[$entityName])) {
            $adminName = $this->interfaceNames[$entityName];
        }

        $adminName = $this->getDomainAdminName($adminName);

        if (!$domain) {
            $domain = new TranslationDomain();
            $domain->setName($domainName);
            $domain->setAdminName(ltrim($adminName, '__'));
        }

        if (in_array($domainName, $toExtract) || preg_match('/front/', $domainName)) {
            $domain->setExtract(true);
            $domain->setForTranslator(true);
        }

        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        return $domain;
    }

    /**
     * Set Unit
     *
     * @param TranslationDomain $domain
     * @param string $keyName
     * @return TranslationUnit
     */
    private function setUnit(TranslationDomain $domain, string $keyName): TranslationUnit
    {
        $unit = $this->unitRepository->findOneBy([
            'domain' => $domain,
            'keyName' => $keyName
        ]);

        if (!$unit) {
            $unit = new TranslationUnit();
            $unit->setKeyname($keyName);
            $unit->setDomain($domain);
            $this->entityManager->persist($unit);
            $this->entityManager->flush();
        }

        return $unit;
    }

    /**
     * Set Translation
     *
     * @param string $defaultLocale
     * @param string $locale
     * @param string $domain
     * @param string|null $content
     * @param string|null $keyName
     * @return bool
     */
    public function generateTranslation(string $defaultLocale, string $locale, string $domain, string $content = NULL, string $keyName = NULL): bool
    {
        $vendorDomains = ['validators', 'security'];
        $defaultDomains = ['admin', 'dev', 'exception', 'form_widget', 'gdpr', 'js_plugins', 'security_cms', 'time', 'validators_cms', 'domain'];
        $disallowed = ['entity_', '_undefined', 'delete_'];

        if ($keyName && !in_array($domain, $disallowed)) {

            $domain = $this->setDomain($domain);
            $isNew = substr($content, 0, 2) === "__";
            $contentFormatted = ltrim($content, '__');
            $unit = $this->setUnit($domain, $keyName);
            $translation = $this->existingTranslation($unit, $locale);
            $isEntityConfiguration = preg_match('/entity_/', $domain->getName());
            $isYamlConfig = false;

            $contentLocale = !$translation && $isEntityConfiguration
            || ($isNew || !$translation) && !in_array($domain->getName(), $defaultDomains) && $locale == $defaultLocale
            || ($isNew || !$translation) && in_array($domain->getName(), $defaultDomains) && $locale == 'fr'
            || in_array($domain->getName(), $vendorDomains)
                ? $contentFormatted : NULL;

            if (!$contentLocale) {
                $contentLocale = $this->getAppYamlTranslation($domain, $keyName, $locale);
                if ($contentLocale) {
                    $contentFormatted = $contentLocale;
                    $isYamlConfig = true;
                }
            }

            if (!$translation) {
                $this->setTranslation($unit, $locale, $contentLocale);
            }

            if ($translation && !$translation->getContent()
                && ($contentFormatted && !in_array($domain->getName(), $defaultDomains) && $locale == $defaultLocale
                    || $contentFormatted && in_array($domain->getName(), $defaultDomains) && $locale == 'fr'
                    || $isEntityConfiguration
                    || $isYamlConfig
                )) {

                $translation->setContent($contentFormatted);
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }
        }

        return true;
    }

    /**
     * Get Default App Translation Yaml (/bin/data/translations)
     *
     * @param TranslationDomain $domain
     * @param string $keyName
     * @param string $locale
     * @return string|null
     */
    private function getAppYamlTranslation(TranslationDomain $domain, string $keyName, string $locale): ?string
    {
        $values = [];
        $filesystem = new Filesystem();
        $baseDirname = $this->kernel->getProjectDir() . '/bin/data/translations/';
        $baseDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirname);

        $filePath = $baseDirname . $domain->getName() . '+intl-icu.' . $locale . '.yaml';
        if ($filesystem->exists($filePath)) {
            $values = Yaml::parseFile($filePath);
        }

        $filePath = $baseDirname . 'translations.' . $locale . '.yaml';
        if ($filesystem->exists($filePath)) {
            $values = Yaml::parseFile($filePath);
        }

        return !empty($values[$keyName]) ? $values[$keyName] : NULL;
    }

    /**
     * Generate translations for Entity configuration
     *
     * @param Website $website
     * @param string $defaultLocale
     * @param array $locales
     */
    public function extractEntities(Website $website, string $defaultLocale, array $locales)
    {
        foreach ($locales as $locale) {
            $this->entityService->execute($website, $locale);
        }

        $entities = $this->entityManager->getRepository(Entity::class)->findAll();
        $values = $this->getCoreValues();

        foreach ($entities as $entity) {

            $this->interfaceHelper->setInterface($entity->getClassName());
            $interface = $this->interfaceHelper->getInterface();
            $interfaceName = !empty($interface['name']) ? $interface['name'] : NULL;
            $config = !empty($values[$entity->getClassName()])
                ? $values[$entity->getClassName()] : [];

            if ($interfaceName) {
                $translationsEntities = $this->setTranslationsEntities($interface, $config, $locales);
                foreach ($translationsEntities as $locale => $translations) {
                    $domainName = 'entity_' . $interfaceName . '+intl-icu.' . $locale . '.yaml';
                    $filePath = $this->kernel->getProjectDir() . '/translations/' . $domainName;
                    $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                    ksort($translations);
                    $yaml = Yaml::dump($translations);
                    file_put_contents($filePath, $yaml);
                }
            }
        }
    }

    /**
     * Get core values
     *
     * @return array
     */
    private function getCoreValues(): array
    {
        $values = [];
        $coreDirname = $this->kernel->getProjectDir() . '/bin/data/fixtures/';
        $coreDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $coreDirname);
        $imports = Yaml::parseFile($coreDirname . 'entity-configuration.yaml')['imports'];
        foreach ($imports as $import) {
            $values = array_merge($values, Yaml::parseFile($coreDirname . $import['resource']));
        }

        return $values;
    }

    /**
     * Get Entities Translations
     *
     * @param array $interface
     * @param array $config
     * @param array $allLocales
     * @return array
     */
    private function setTranslationsEntities(array $interface = [], array $config = [], array $allLocales = []): array
    {
        $translations = [];

        foreach ($allLocales as $locale) {
            foreach ($this->entityFields as $field) {
                $translations[$locale][$field] = NULL;
            }
        }

        if (!empty($interface['labels'])) {
            foreach ($interface['labels'] as $keyName => $value) {
                $translations['fr'][$keyName] = $value;
            }
        }

        if (!empty($config['translations'])) {
            foreach ($config['translations'] as $keyName => $locales) {
                foreach ($locales as $locale => $value) {
                    $translations[$locale][$keyName] = $value;
                }
            }
        }

        foreach ($translations as $translation) {
            foreach ($translation as $keyName => $content) {
                foreach ($allLocales as $locale) {
                    if (!isset($translations[$locale][$keyName])) {
                        $translations[$locale][$keyName] = NULL;
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Check if translation already exist
     *
     * @param TranslationUnit $unit
     * @param $locale
     * @return Translation|null
     */
    private function existingTranslation(TranslationUnit $unit, $locale): ?Translation
    {
        return $this->translationRepository->findOneBy([
            'unit' => $unit,
            'locale' => $locale
        ]);
    }

    /**
     * Generate DB Translation
     *
     * @param TranslationUnit $unit
     * @param string $locale
     * @param string|NULL $content
     */
    private function setTranslation(TranslationUnit $unit, string $locale, string $content = NULL)
    {
        $translation = new Translation();
        $translation->setLocale($locale);
        $translation->setContent(str_replace(['{{', '}}'], ['{', '}'], $content));
        $translation->setUnit($unit);
        $this->entityManager->persist($translation);
        $this->entityManager->flush();
    }

    /**
     * Remove extract yaml files & generate .db files
     *
     * @param array $locales
     */
    public function initFiles(array $locales)
    {
        $filesystem = new Filesystem();
        $baseDirname = $this->kernel->getProjectDir() . '/translations/';
        $baseDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirname);

        /** Remove extract yaml files */
        $finder = new Finder();
        $finder->in($baseDirname)->name('*.yaml')->name('*.yml');
        $filesystem->remove($finder);

        /** Add .db files */
        $domains = $this->domainRepository->findAll();
        foreach ($domains as $domain) {
            foreach ($locales as $locale) {
                $fileDbPath = $baseDirname . $domain->getName() . '.' . $locale . '.db';
                file_put_contents($fileDbPath, '');
            }
        }
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $filesystem = new Filesystem();
        $filesystem->remove([$this->kernel->getCacheDir() . '/translations']);
        $filesystem->remove([$this->kernel->getCacheDir() . '/website']);
    }

    /**
     * Get domain AdminName
     *
     * @param string $domain
     * @return string
     */
    private function getDomainAdminName(string $domain): string
    {
        $domain = str_replace('+intl-icu', '', $domain);

        $domains = [
            'admin' => $this->translator->trans('Administration', [], 'domain'),
            'domain' => $this->translator->trans('Symfony domain', [], 'domain'),
            'error' => $this->translator->trans('Erreur', [], 'domain'),
            'exception' => $this->translator->trans('Exception', [], 'domain'),
            'front_default' => $this->translator->trans('Site principal', [], 'domain'),
            'front_webmaster' => $this->translator->trans('Webmaster tools box', [], 'domain'),
            'front' => $this->translator->trans('Site', [], 'domain'),
            'front_form' => $this->translator->trans('Formulaires site', [], 'domain'),
            'front_js_plugins' => $this->translator->trans('Plugins javaScript (Site)', [], 'domain'),
            'gdpr' => $this->translator->trans('Gestion des Cookies', [], 'domain'),
            'admin_js_plugins' => $this->translator->trans('Plugins javaScript (Administration)', [], 'domain'),
            'security' => $this->translator->trans('Sécurité', [], 'domain'),
            'security_cms' => $this->translator->trans('Sécurité CMS', [], 'domain'),
            'time' => $this->translator->trans('Extension doctrine', [], 'domain'),
            'validators' => $this->translator->trans('Validations', [], 'domain'),
            'validators_cms' => $this->translator->trans('Validations CMS', [], 'domain'),
            'build' => $this->translator->trans('Page de maintenance', [], 'domain'),
            'email' => $this->translator->trans('Emails', [], 'domain'),
            'ie_alert' => $this->translator->trans('Alerte Internet Explorer', [], 'domain'),
            'core_init' => $this->translator->trans('Initialisation de projet', [], 'domain'),
            'messages' => $this->translator->trans('Général', [], 'domain'),
        ];

        return !empty($domains[$domain]) ? $domains[$domain] : $domain;
    }

    /**
     * Set Interface names
     */
    private function setInterfaceNames()
    {
        $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metasData as $metaData) {
            if (!preg_match('/Base/', $metaData->getName())) {
                $this->interfaceHelper->setInterface($metaData->getName());
                $interface = $this->interfaceHelper->getInterface();
                if (!empty($interface['name'])) {
                    $this->interfaceNames[$interface['name']] = $interface['classname'];
                }
            }
        }
    }
}