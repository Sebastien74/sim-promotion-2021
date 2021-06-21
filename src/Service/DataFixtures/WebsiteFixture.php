<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Repository\Core\WebsiteRepository;
use App\Service\Development\EntityService;
use App\Service\Core\SubscriberService;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * WebsiteFixture
 *
 * Website Fixture management
 *
 * @property array DEFAULTS_MODULES
 * @property array OTHERS_MODULES
 * @property bool DEV_MODE
 *
 * @property array $websites
 * @property SubscriberService $subscriber
 * @property EntityService $entityService
 * @property WebsiteRepository $websiteRepository
 * @property KernelInterface $kernel
 * @property array $yamlConfiguration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteFixture
{
    private const DEV_MODE = false;
    private const DEFAULTS_MODULES = [
        'ROLE_EDIT',
        'ROLE_PAGE',
        'ROLE_NEWSCAST',
        'ROLE_SEO',
        'ROLE_SLIDER',
        'ROLE_TRANSLATION',
        'ROLE_NAVIGATION',
    ];
    private const OTHERS_MODULES = [
        'ROLE_MEDIA',
        'ROLE_USERS',
        'ROLE_INFORMATION',
        'ROLE_FORM',
        'ROLE_STEP_FORM',
        'ROLE_GALLERY',
        'ROLE_TABLE',
        'ROLE_MAP',
        'ROLE_SITE_MAP',
        'ROLE_SOCIAL_WALL',
        'ROLE_TAB',
        'ROLE_SEARCH_ENGINE',
        'ROLE_MAKING',
        'ROLE_CONTACT',
        'ROLE_FAQ',
        'ROLE_AGENDA',
        'ROLE_PORTFOLIO',
        'ROLE_FORM_CALENDAR',
        'ROLE_FORUM',
        'ROLE_CATALOG',
        'ROLE_NEWSLETTER',
        'ROLE_TIMELINE',
        'ROLE_SECURE_PAGE',
        'ROLE_ANALYTICS',
    ];

    private $websites;
    private $subscriber;
    private $entityService;
    private $websiteRepository;
    private $kernel;
    private $yamlConfiguration = [];

    /**
     * WebsiteFixture constructor.
     *
     * @param SubscriberService $subscriber
     * @param EntityService $entityService
     * @param WebsiteRepository $websiteRepository
     * @param KernelInterface $kernel
     */
    public function __construct(
        SubscriberService $subscriber,
        EntityService $entityService,
        WebsiteRepository $websiteRepository,
        KernelInterface $kernel)
    {
        $this->subscriber = $subscriber;
        $this->entityService = $entityService;
        $this->websiteRepository = $websiteRepository;
        $this->kernel = $kernel;
    }

    /**
     * Get Yaml Website configuration
     *
     * @param string|null $yamlConfigDirname
     * @throws Exception
     */
    private function getYamlConfiguration(string $yamlConfigDirname = NULL)
    {
        $this->websites = $this->websiteRepository->findAll();
        $filesystem = new Filesystem();
        $configDirname = $this->kernel->getProjectDir() . '/bin/data/config/';
        $configDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configDirname);
        $configFileDirname = count($this->websites) === 0 ? $configDirname . 'default.yaml' : ($yamlConfigDirname ? $configDirname . $yamlConfigDirname . '.yaml' : NULL);

        if ($configFileDirname && !is_dir($configFileDirname) && $filesystem->exists($configFileDirname)) {

            $configuration = Yaml::parseFile($configFileDirname);
            $this->yamlConfiguration = is_array($configuration) ? $configuration : $this->yamlConfiguration;

            if (isset($configuration['is_init']) && !$configuration['is_init']) {
                throw new Exception('Parameter "is_init" in your yaml configuration file is false!!');
            }
        }
    }

    /**
     * Initialize Website
     *
     * @param Website $website
     * @param string $locale
     * @param User|null $user
     * @param string|null $yamlConfigDirname
     * @throws Exception
     */
    public function initialize(Website $website, string $locale, User $user = NULL, string $yamlConfigDirname = NULL)
    {
        $this->getYamlConfiguration($yamlConfigDirname);

        if ($user) {
            $website->setCreatedBy($user);
        }
        if (count($this->websites) === 0) {
            $website->setActive(true);
        }

        $website->setCacheClearDate(new \DateTime('now'));
        $website->setUploadDirname(uniqid());

        $pagesParams = $this->getPagesParams();
        $yamlConfiguration = $this->yamlConfiguration;
        $locale = !empty($yamlConfiguration['locale']) ? $yamlConfiguration['locale'] : $locale;

        $this->subscriber->get(ConfigurationFixture::class)->add($website, $yamlConfiguration, $locale, self::DEV_MODE, self::DEFAULTS_MODULES, self::OTHERS_MODULES, $user);
        $this->subscriber->get(SecurityFixture::class)->execute($website);
        $configuration = $website->getConfiguration();
        $this->subscriber->get(InformationFixture::class)->add($website, $yamlConfiguration, $user);
        $this->subscriber->get(ApiFixture::class)->add($website, $yamlConfiguration);
        $this->subscriber->get(SeoFixture::class)->add($website, $user);
        $webmasterFolder = $this->subscriber->get(DefaultMediasFixture::class)->add($website, $yamlConfiguration, $user);
        $this->subscriber->get(BlockTypeFixture::class)->add($configuration, self::DEV_MODE);
        $this->subscriber->get(ColorFixture::class)->add($configuration, $yamlConfiguration, $user);
        $this->subscriber->get(TransitionFixture::class)->add($configuration, $user);
        $this->subscriber->get(NewscastFixture::class)->add($website, $pagesParams, $user);
        $this->subscriber->get(NewsletterFixture::class)->add($website, $user);
        $pages = $this->subscriber->get(PageFixture::class)->add($website, $pagesParams, $user);
        $this->subscriber->get(LayoutFixture::class)->add($configuration, self::DEV_MODE, self::DEFAULTS_MODULES, self::OTHERS_MODULES, $user);
        $this->subscriber->get(MenuFixture::class)->add($website, $pages, $pagesParams, $user);
        $this->subscriber->get(GdprFixture::class)->add($webmasterFolder, $website, $user);
        $this->subscriber->get(MapFixture::class)->add($webmasterFolder, $website, $user);
        $this->entityService->setWebsite($website);
        $this->entityService->setCreatedBy($user);
        $this->entityService->execute($website, $locale);
        $this->subscriber->get(ThumbnailFixture::class)->add($website, $user);
        $this->subscriber->get(TodoFixture::class)->add($website, $user);
        $this->subscriber->get(CommandFixture::class)->add($website, $user);
        $this->subscriber->get(TranslationsFixture::class)->generate($configuration, $this->websites);

        $session = new Session();
        $session->remove('adminWebsite');
        $session->remove('frontWebsite');
    }

    /**
     * Get Pages[] params
     *
     * @return array
     */
    private function getPagesParams(): array
    {
        return [
            ['name' => 'Accueil', 'isIndex' => true, 'reference' => 'home', 'menu' => false, 'template' => 'home', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Actualités', 'isIndex' => false, 'reference' => 'news', 'menu' => 'main', 'template' => 'news', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Écrivez-nous', 'isIndex' => false, 'reference' => 'contact', 'menu' => 'main', 'template' => 'contact', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Plan de site', 'isIndex' => false, 'reference' => 'sitemap', 'menu' => 'footer', 'template' => 'cms', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Mentions légales', 'isIndex' => false, 'reference' => 'legale', 'menu' => 'footer', 'template' => 'legacy', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Politique relative aux cookies', 'isIndex' => false, 'reference' => 'cookies', 'menu' => 'footer', 'template' => 'legacy', 'urlIsIndex' => true, 'deletable' => true],
            ['name' => 'Components', 'isIndex' => false, 'reference' => 'components', 'menu' => 'main', 'template' => 'components', 'urlIsIndex' => false, 'deletable' => true],
            ['name' => 'Erreurs', 'isIndex' => false, 'reference' => 'error', 'menu' => false, 'template' => 'error', 'urlIsIndex' => false, 'deletable' => false]
        ];
    }
}