<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Module;
use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Configuration;
use App\Entity\Core\Security;
use App\Entity\Core\Website;
use App\Entity\Information\Email;
use App\Entity\Layout\Page;
use App\Entity\Translation\i18n;
use App\Service\DataFixtures\WebsiteFixture;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * WebsiteManager
 *
 * Manage admin Website form
 *
 * @property WebsiteFixture $fixtures
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteManager
{
    private $fixtures;
    private $entityManager;
    private $kernel;

    /**
     * WebsiteManager constructor.
     *
     * @param WebsiteFixture $fixtures
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(WebsiteFixture $fixtures, EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->fixtures = $fixtures;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * @prePersist
     *
     * @param Website $website
     * @param Website $currentWebsite
     * @param array $interface
     * @param Form $form
     * @throws Exception
     */
    public function prePersist(Website $website, Website $currentWebsite, array $interface, Form $form)
    {
        $this->fixtures->initialize($website, $website->getConfiguration()->getLocale(), NULL, $form->get('yaml_config')->getData());
    }

    /**
     * @preUpdate
     *
     * @param Website $website
     */
    public function preUpdate(Website $website)
    {
        $configuration = $website->getConfiguration();
        $locale = $configuration->getLocale();
        $locales = $configuration->getLocales();

        /** Remove default locale if in locales */
        if (in_array($locale, $locales)) {
            unset($locales[array_search($locale, $locales)]);
            $configuration->setLocales($locales);
        }

        $this->setModules($configuration);
        $this->setGdpr($website);
        $this->setSecurity($website);
        $this->setEmails($website, $locale, $locales);
        $this->setFramework($configuration);
    }

    /**
     * To set modules
     *
     * @param Configuration $configuration
     */
    private function setModules(Configuration $configuration)
    {
        $associateModules = [
            'form-calendar' => 'form',
            'real-estate-programs' => 'catalog',
        ];

        foreach ($associateModules as $code => $associateCode) {
            foreach ($configuration->getModules() as $module) {
                if ($module->getSlug() === $code) {
                    $associateModuleExist = false;
                    foreach ($configuration->getModules() as $moduleDb) {
                        if($moduleDb->getSlug() === $associateCode) {
                            $associateModuleExist = true;
                            break;
                        }
                    }
                    if(!$associateModuleExist) {
                        $associateModule = $this->entityManager->getRepository(Module::class)->findOneBy(['slug' => $associateCode]);
                        if($associateModule instanceof Module) {
                            $configuration->addModule($associateModule);
                        }
                    }
                }
            }
        }
    }

    /**
     * To active GDPR contents
     *
     * @param Website $website
     */
    private function setGdpr(Website $website)
    {
        $configuration = $website->getConfiguration();
        $gdprActive = $this->moduleActive($configuration, 'gdpr');

        if ($gdprActive) {

            $footerMenu = $this->getMenu($website, 'footer');
            $cookiesPage = $this->getPage($website, 'cookies');

            /** Active urls */
            if ($cookiesPage) {
                foreach ($cookiesPage->getUrls() as $url) {
                    $url->setIsOnline(true);
                }
            }

            if ($footerMenu && $cookiesPage) {
                foreach ($configuration->getOnlineLocales() as $locale) {
                    $existingLink = $this->entityManager->getRepository(Link::class)->findByPageAndLocale($website, $cookiesPage, $locale);
                    if (!$existingLink) {
                        $this->addLink($footerMenu, $locale, $cookiesPage, $website);
                    }
                }
            }
        }
    }

    /**
     * To set security
     *
     * @param Website $website
     */
    private function setSecurity(Website $website)
    {
        $security = $website->getSecurity();
        $websites = $this->entityManager->getRepository(Website::class)->findAll();

        foreach ($websites as $websiteDb) {
            if ($websiteDb->getId() !== $website->getId()) {
                /** @var Security $securityDb */
                $securityDb = $websiteDb->getSecurity();
                $securityDb->setAdminPasswordDelay($security->getAdminPasswordDelay());
                $this->entityManager->persist($securityDb);
            }
        }
    }

    /**
     * To set required locales emails
     *
     * @param Website $website
     * @param string $locale
     * @param array $locales
     */
    private function setEmails(Website $website, string $locale, array $locales)
    {
        $slugs = ['support', 'no-reply'];
        $information = $website->getInformation();
        $defaultLocaleEmails = [];

        foreach ($information->getEmails() as $email) {
            if ($email->getLocale() === $locale && in_array($email->getSlug(), $slugs)) {
                $defaultLocaleEmails[$email->getSlug()] = $email;
            }
        }

        foreach ($slugs as $slug) {

            $existing = false;

            foreach ($locales as $locale) {

                foreach ($information->getEmails() as $email) {
                    if ($email->getLocale() === $locale && $email->getSlug() === $slug) {
                        $existing = true;
                    }
                }

                if (!$existing && !empty($defaultLocaleEmails[$slug])) {
                    $newEmail = new Email();
                    $newEmail->setSlug($slug);
                    $newEmail->setLocale($locale);
                    $newEmail->setEmail($defaultLocaleEmails[$slug]->getEmail());
                    $newEmail->setZones($defaultLocaleEmails[$slug]->getZones());
                    $newEmail->setDeletable(false);
                    $information->addEmail($newEmail);
                    $this->entityManager->persist($information);
                }
            }
        }
    }

    /**
     * Set Yaml framework configuration
     *
     * @param Configuration $configuration
     */
    private function setFramework(Configuration $configuration)
    {
        $allLocales = $configuration->getAllLocales();

        $filePath = $this->kernel->getProjectDir() . '/config/packages/translation.yaml';
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        $filesystem = new Filesystem();

        if ($filesystem->exists($filePath)) {

            $values = Yaml::parseFile($filePath);
            $enabledLocales = $values['framework']['translator']['enabled_locales'];

            $yamlLocalesCount = 0;
            foreach ($enabledLocales as $locale) {
                if(in_array($locale, $allLocales)) {
                    $yamlLocalesCount++;
                }
            }

            if($yamlLocalesCount !== count($allLocales)) {
                $values['framework']['translator']['enabled_locales'] = $allLocales;
                $yaml = Yaml::dump($values);
                file_put_contents($filePath, $yaml);
                if($this->kernel->getEnvironment() === 'prod') {
                    $filesystem->remove([$this->kernel->getCacheDir()]);
                }
            }
        }
    }

    /**
     * Check if module is activated
     *
     * @param Configuration $configuration
     * @param string $slug
     * @return bool
     */
    private function moduleActive(Configuration $configuration, string $slug): bool
    {
        $active = false;

        foreach ($configuration->getModules() as $module) {
            if ($module->getSlug() === $slug) {
                $active = true;
                break;
            }
        }

        return $active;
    }

    /**
     * Get page by slug
     *
     * @param Website $website
     * @param string $slug
     * @return mixed
     */
    private function getMenu(Website $website, string $slug)
    {
        return $this->entityManager->getRepository(Menu::class)->findOneBy([
            'website' => $website,
            'slug' => $slug
        ]);
    }

    /**
     * Get page by slug
     *
     * @param Website $website
     * @param string $slug
     * @return mixed
     */
    private function getPage(Website $website, string $slug)
    {
        return $this->entityManager->getRepository(Page::class)->findOneBy([
            'website' => $website,
            'slug' => $slug
        ]);
    }

    /**
     * Add Link to Menu
     *
     * @param Menu $menu
     * @param string $locale
     * @param Page $page
     * @param Website $website
     */
    private function addLink(Menu $menu, string $locale, Page $page, Website $website)
    {
        $linkPosition = count($this->entityManager->getRepository(Link::class)->findBy([
                'menu' => $menu,
                'locale' => $locale
            ])) + 1;

        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setTitle($page->getAdminName());
        $i18n->setWebsite($website);
        $i18n->setTargetPage($page);

        $link = new Link();
        $link->setAdminName($page->getAdminName());
        $link->setMenu($menu);
        $link->setPosition($linkPosition);
        $link->setLocale($locale);
        $link->setI18n($i18n);

        $this->entityManager->persist($i18n);
        $this->entityManager->persist($link);
    }
}