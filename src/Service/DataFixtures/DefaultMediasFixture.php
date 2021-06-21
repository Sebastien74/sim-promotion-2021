<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * DefaultMediasFixture
 *
 * DefaultMedia Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property UploadedFileFixture $uploader
 * @property KernelInterface $kernel
 * @property array $yamlConfiguration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DefaultMediasFixture
{
    private $entityManager;
    private $uploader;
    private $kernel;
    private $yamlConfiguration = [];

    /**
     * DefaultMediasFixture constructor.
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
     * Add defaults Medias
     *
     * @param Website $website
     * @param array $yamlConfiguration
     * @param User|null $user
     * @return Folder
     */
    public function add(Website $website, array $yamlConfiguration, User $user = NULL): Folder
    {
        $this->yamlConfiguration = $yamlConfiguration;

        $configuration = $website->getConfiguration();
        $locale = $configuration->getLocale();
        $projectPath = !empty($yamlConfiguration['media_path_duplication']) ? $yamlConfiguration['media_path_duplication'] : 'default';

        $webmasterFolder = $this->uploader->generateFolder($website, 'Webmaster', 'webmaster', NULL, $user);
        $mainFolder = $this->uploader->generateFolder($website, 'Images principales', 'default-media', $webmasterFolder, $user);
        $this->uploader->generateFolder($website, 'Pictos', 'pictogram', $webmasterFolder, $user);
        $this->uploader->generateFolder($website, 'Pages', 'page', NULL, $user, false);
        $this->uploader->generateFolder($website, 'Actualités', 'newscast', NULL, $user, false);
        $this->uploader->generateFolder($website, 'Carousels', 'slider', NULL, $user, false);
        $this->uploader->generateFolder($website, 'Menus', 'menu', NULL, $user, false);

        foreach ($this->getMedias() as $keyName => $infos) {
            $filename = !empty($yamlConfiguration['files'][$keyName]) ? $yamlConfiguration['files'][$keyName] : $infos->filename;
            $directory = $infos->doc ? 'docs' : 'images';
            $path = $this->kernel->getProjectDir() . '/assets/medias/' . $directory . '/' . $projectPath . '/' . $filename;
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            $media = $this->uploader->uploadedFile($website, $path, $locale, $configuration, $infos->category, $keyName, $user);
            if($media instanceof Media) {
                $media->setFolder($mainFolder);
                $this->entityManager->persist($media);
                $this->entityManager->flush();
            }
        }

        return $webmasterFolder;
    }

    /**
     * Get Media[] configurations
     */
    private function getMedias(): array
    {
        return [
            'logo' => (object)['category' => 'logo', 'filename' => 'logo.svg', 'doc' => false],
            'favicon' => (object)['category' => 'favicon', 'filename' => 'favicon.ico', 'doc' => false],
            'favicon-apple-touch-icon' => (object)['category' => 'favicon-apple-touch-icon', 'filename' => 'apple-touch-icon.png', 'doc' => false],
            'favicon-16x16' => (object)['category' => 'favicon-16x16', 'filename' => 'favicon-16x16.png', 'doc' => false],
            'favicon-32x32' => (object)['category' => 'favicon-32x32', 'filename' => 'favicon-32x32.png', 'doc' => false],
            'mstile-150x150' => (object)['category' => 'mstile-150x150', 'filename' => 'mstile-150x150.png', 'doc' => false],
            'android-chrome-144x144' => (object)['category' => 'android-chrome-144x144', 'filename' => 'android-chrome-144x144.png', 'doc' => false],
            'android-chrome-192x192' => (object)['category' => 'android-chrome-192x192', 'filename' => 'android-chrome-192x192.png', 'doc' => false],
            'android-chrome-512x512' => (object)['category' => 'android-chrome-512x512', 'filename' => 'android-chrome-512x512.png', 'doc' => false],
            'mask-icon' => (object)['category' => 'mask-icon', 'filename' => 'safari-pinned-tab.svg', 'doc' => false],
            'share' => (object)['category' => 'share', 'filename' => 'share.jpg', 'doc' => false],
            'preloader' => (object)['category' => 'preloader', 'filename' => 'preloader.svg', 'doc' => false],
            'footer' => (object)['category' => 'footer', 'filename' => 'footer-logo.svg', 'doc' => false],
            'email' => (object)['category' => 'email', 'filename' => 'email-logo.svg', 'doc' => false],
            'admin' => (object)['category' => 'admin', 'filename' => 'admin-logo.svg', 'doc' => false],
            'titleheader' => (object)['category' => 'titleheader', 'filename' => 'titleheader.jpg', 'doc' => false],
            'placeholder' => (object)['category' => 'placeholder', 'filename' => 'placeholder.jpg', 'doc' => false],
            'facebook' => (object)['category' => 'social-network', 'filename' => 'facebook.svg', 'doc' => false],
            'google-plus' => (object)['category' => 'social-network', 'filename' => 'google-plus.svg', 'doc' => false],
            'twitter' => (object)['category' => 'social-network', 'filename' => 'twitter.svg', 'doc' => false],
            'youtube' => (object)['category' => 'social-network', 'filename' => 'youtube.svg', 'doc' => false],
            'instagram' => (object)['category' => 'social-network', 'filename' => 'instagram.svg', 'doc' => false],
            'linkedin' => (object)['category' => 'social-network', 'filename' => 'linkedin.svg', 'doc' => false],
            'pinterest' => (object)['category' => 'social-network', 'filename' => 'pinterest.svg', 'doc' => false],
            'tripadvisor' => (object)['category' => 'social-network', 'filename' => 'tripadvisor.svg', 'doc' => false]
        ];
    }
}