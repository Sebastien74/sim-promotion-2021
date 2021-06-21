<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ConfigurationFixture
 *
 * Configuration Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property array $defaultsModules
 * @property array $yamlConfiguration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationFixture
{
    private $entityManager;
    private $kernel;
    private $yamlConfiguration = [];

    /**
     * ConfigurationFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * Add Configuration
     *
     * @param Website $website
     * @param array $yamlConfiguration
     * @param string $locale
     * @param bool $devMode
     * @param array $defaultsModules
     * @param array $othersModules
     * @param User|null $user
     * @return Configuration
     * @throws Exception
     */
    public function add(Website $website, array $yamlConfiguration, string $locale, bool $devMode, array $defaultsModules, array $othersModules, User $user = NULL): Configuration
    {
        $this->yamlConfiguration = $yamlConfiguration;

        $configuration = $this->addConfiguration($locale, $website, $yamlConfiguration, $user);

        $this->addModules($configuration, $devMode, $defaultsModules, $othersModules);
        $this->addDomains($configuration);
        $this->addFonts($configuration);

        $website->setConfiguration($configuration);

        $this->entityManager->persist($configuration);

        return $configuration;
    }

    /**
     * Add Configuration
     *
     * @param string $locale
     * @param Website $website
     * @param array $yamlConfiguration
     * @param User|null $user
     * @return Configuration
     */
    private function addConfiguration(string $locale, Website $website, array $yamlConfiguration, User $user = NULL): Configuration
    {
        $template = !empty($yamlConfiguration['template']) ? $yamlConfiguration['template']
            : ($website->getConfiguration() instanceof Configuration ? $website->getConfiguration()->getTemplate() : 'default');
        $locales = !empty($this->yamlConfiguration['locales_others']) ? $this->yamlConfiguration['locales_others']
            : ($website->getConfiguration() instanceof Configuration ? $website->getConfiguration()->getLocales() : []);
        $onlineLocales = !empty($locales) ? array_merge($locales, [$locale]) : [$locale];

        $configuration = $website->getConfiguration() instanceof Configuration ? $website->getConfiguration() : new Configuration();
        $configuration->setLocale($locale);
        $configuration->setLocales($locales);
        $configuration->setOnlineLocales($onlineLocales);
        $configuration->setTemplate($template);

        if ($user) {
            $configuration->setCreatedBy($user);
        }

        $configuration->setWebsite($website);

        return $configuration;
    }

    /**
     * Add Modules
     *
     * @param Configuration $configuration
     * @param bool $devMode
     * @param array $defaultsModules
     * @param array $othersModules
     */
    private function addModules(Configuration $configuration, bool $devMode, array $defaultsModules, array $othersModules)
    {
        $modules = $this->entityManager->getRepository(Module::class)->findAll();
        $modulesToAdd = $devMode ? array_merge($defaultsModules, $othersModules) : $defaultsModules;

        foreach ($modules as $module) {
            /** @var Module $module */
            if (in_array($module->getRole(), $modulesToAdd) || $module->getSlug() === 'gdpr') {
                $configuration->addModule($module);
            }
        }
    }

    /**
     * Add Domains
     *
     * @param Configuration $configuration
     */
    private function addDomains(Configuration $configuration)
    {
        if (!empty($this->yamlConfiguration['domains']) && is_array($this->yamlConfiguration['domains'])) {

            $repository = $this->entityManager->getRepository(Domain::class);
            $position = count($repository->findBy(['configuration' => $configuration])) + 1;

            foreach ($this->yamlConfiguration['domains'] as $locale => $domains) {
                foreach ($domains as $domainName => $hasDefault) {

                    $existing = $repository->findBy(['name' => $domainName]);

                    if(!$existing) {
                        $domain = new Domain();
                        $domain->setName($domainName);
                        $domain->setLocale($locale);
                        $domain->setPosition($position);
                        $domain->setHasDefault($hasDefault);
                        $configuration->addDomain($domain);
                        $position++;
                    }
                }
            }
        }
    }

    /**
     * Add Font
     *
     * @param Configuration $configuration
     * @throws Exception
     */
    private function addFonts(Configuration $configuration)
    {
        $fonts = !empty($this->yamlConfiguration['fonts']) ? $this->yamlConfiguration['fonts'] : $configuration->getFrontFonts();

        $filesystem = new Filesystem();
        $fontsDirname = $this->kernel->getProjectDir() . '/assets/lib/fonts/';
        $fontsDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fontsDirname);

        foreach ($fonts as $font) {
            $fontDirname = $fontsDirname . $font . '.scss';
            if (!$filesystem->exists($fontDirname)) {
                throw new Exception($fontDirname . " file doesn't exist!!");
            }
        }

        $configuration->setFrontFonts($fonts);
    }
}