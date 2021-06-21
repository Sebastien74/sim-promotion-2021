<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Gdpr\Category;
use App\Entity\Gdpr\Cookie;
use App\Entity\Gdpr\Group;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * GdprFixture
 *
 * Gdpr Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property UploadedFileFixture $uploader
 * @property KernelInterface $kernel
 * @property Folder $mediaFolder
 * @property Website $website
 * @property Configuration $configuration
 * @property User $user
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GdprFixture
{
    private $entityManager;
    private $uploader;
    private $kernel;
    private $mediaFolder;
    private $website;
    private $configuration;
    private $user;

    /**
     * GdprFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UploadedFileFixture $uploader
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, UploadedFileFixture $uploader, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->uploader = $uploader;
        $this->kernel = $kernel;
    }

    /**
     * Add GDPR entities
     *
     * @param Folder $webmasterFolder
     * @param Website $website
     * @param User|NULL $user
     */
    public function add(Folder $webmasterFolder, Website $website, User $user = NULL)
    {
        $this->website = $website;
        $this->configuration = $website->getConfiguration();
        $this->mediaFolder = $this->uploader->generateFolder($website, 'RGPD', 'gdpr', $webmasterFolder, $user);
        $this->user = $user;

        foreach ($this->getCategoriesParams() as $key => $categoryParams) {
            $category = $this->generateCategory($categoryParams, ($key + 1));
            $this->generateGroup($category);
        }

        $this->entityManager->flush();
    }

    /**
     * Get Category[] params
     *
     * @return array
     */
    private function getCategoriesParams(): array
    {
        return [
            ['slug' => 'functional', 'name' => "Cookies de fonctionnement"],
            ['slug' => 'display', 'name' => "Cookies d'affichage"],
            ['slug' => 'audience', 'name' => "Cookies de mesure d’audience"],
            ['slug' => 'social', 'name' => "Cookies des réseaux sociaux"],
            ['slug' => 'marketing', 'name' => "Cookies marketing et autres cookies"],
        ];
    }

    /**
     * Generate Category
     *
     * @param array $categoryParams
     * @param int $position
     * @return Category
     */
    private function generateCategory(array $categoryParams, int $position): Category
    {
        $params = (object)$categoryParams;

        $category = new Category();
        $category->setAdminName($params->name);
        $category->setSlug($params->slug);
        $category->setPosition($position);
        $category->setConfiguration($this->configuration);

        if ($this->user) {
            $category->setCreatedBy($this->user);
        }

        $this->entityManager->persist($category);

        return $category;
    }

    /**
     * Get Group[] params
     *
     * @param string $slug
     * @return array
     */
    private function getGroupsParams(string $slug): array
    {
        $path = $this->kernel->getProjectDir() . '/bin/data/fixtures/gdpr-group.yaml';
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $groups = Yaml::parseFile($path);

        return !empty($groups[$slug]) ? $groups[$slug] : [];
    }

    /**
     * Generate Group
     *
     * @param Category $category
     */
    private function generateGroup(Category $category)
    {
        $groups = $this->getGroupsParams($category->getSlug());
        $position = 1;

        foreach ($groups as $name => $groupParams) {

            $groupParams = (object)$groupParams;
            $anonymize = property_exists($groupParams, 'anonymize') ? $groupParams->anonymize : false;

            $group = new Group();
            $group->setAdminName($name);
            $group->setActive($groupParams->active);
            $group->setAnonymize($anonymize);
            $group->setService($groupParams->service);
            $group->setGdprcategory($category);
            $group->setPosition($position);

            if ($this->user) {
                $group->setCreatedBy($this->user);
            }

            $this->entityManager->persist($group);
            $this->entityManager->flush();

            $position++;

            foreach ($groupParams->i18ns as $locale => $i18nConfig) {

                $i18nConfig = (object)$i18nConfig;
                $i18n = new i18n();
                $i18n->setLocale($locale);
                $i18n->setWebsite($this->website);
                $i18n->setTitle($name);
                $i18n->setIntroduction('<p>' . $i18nConfig->introduction . '</p>');
                $i18n->setBody('<p>' . $i18nConfig->body . '</p>');
                $i18n->setTargetLink($i18nConfig->link);

                if ($this->user) {
                    $i18n->setCreatedBy($this->user);
                }

                $group->addI18n($i18n);

                $this->entityManager->persist($i18n);

                $path = $this->kernel->getProjectDir() . '/assets/medias/images/gdpr/' . $group->getSlug() . '-gdpr.svg';
                $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                $media = $this->uploader->uploadedFile($this->website, $path, $locale, $group, "gdpr", "gdpr", $this->user);

                if ($media instanceof Media) {
                    $media->setFolder($this->mediaFolder);
                    $this->entityManager->persist($media);
                    $this->entityManager->flush();
                }
            }

            $this->setCookies($group);
        }
    }

    /**
     * Generate Cookies
     *
     * @param Group $group
     */
    private function setCookies(Group $group)
    {
        $cookies = $this->getCookies($group->getSlug());

        foreach ($cookies as $key => $cookieName) {

            $cookie = new Cookie();
            $cookie->setAdminName($cookieName);
            $cookie->setPosition($key + 1);
            $cookie->setSlug($cookieName . '-' . $group->getSlug());
            $cookie->setCode($cookieName);
            $cookie->setGdprgroup($group);

            if ($this->user) {
                $cookie->setCreatedBy($this->user);
            }

            $this->entityManager->persist($cookie);
        }
    }

    /**
     * Get Cookie[] params
     *
     * @param string $slug
     * @return array
     */
    private function getCookies(string $slug): array
    {
        $cookies['php'] = ['PHPSESSID', 'REMEMBERME'];
        $cookies['google-analytics'] = ['_ga', '_gat', '_gid'];
        $cookies['google-tag-manager'] = ['_ga', '_gid'];
        $cookies['tawk-to'] = ['TawkConnectionTime', '__tawkuuid'];
        $cookies['facebook'] = ['fr'];

        return !empty($cookies[$slug]) ? $cookies[$slug] : [];
    }
}