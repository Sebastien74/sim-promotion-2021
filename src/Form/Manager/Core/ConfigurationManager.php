<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Color;
use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Information\SocialNetwork;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use App\Twig\Content\ColorRuntime;
use App\Twig\Content\MediaRuntime;
use App\Twig\Translation\i18nRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ConfigurationManager
 *
 * Manage admin Configuration form
 *
 * @property array ADD_CUSTOM_DEFAULT_MEDIAS
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Request $request
 * @property i18nRuntime $i18nRuntime
 * @property ColorRuntime $colorRuntime
 * @property MediaRuntime $mediaRuntime
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationManager
{
    private const ADD_CUSTOM_DEFAULT_MEDIAS = [
        'webmanifest',
        'security-logo',
        'security-bg',
        'build-logo',
        'build-bg',
        'user-condition'
    ];

    private $entityManager;
    private $kernel;
    private $request;
    private $i18nRuntime;
    private $colorRuntime;
    private $mediaRuntime;

    /**
     * ConfigurationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param RequestStack $requestStack
     * @param i18nRuntime $i18nRuntime
     * @param ColorRuntime $colorRuntime
     * @param MediaRuntime $mediaRuntime
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        RequestStack $requestStack,
        i18nRuntime $i18nRuntime,
        ColorRuntime $colorRuntime,
        MediaRuntime $mediaRuntime)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->request = $requestStack->getMasterRequest();
        $this->i18nRuntime = $i18nRuntime;
        $this->colorRuntime = $colorRuntime;
        $this->mediaRuntime = $mediaRuntime;
    }

    /**
     * Synchronize locale relation entities
     *
     * @param Configuration $configuration
     */
    public function synchronizeLocales(Configuration $configuration)
    {
        $this->synchronizeMedias($configuration);
        $this->synchronizeNetworks($configuration);
    }

    /**
     * @preUpdate
     *
     * @param Configuration $configuration
     */
    public function preUpdate(Configuration $configuration)
    {
        $this->generateStylesheetFile($configuration);
    }

    /**
     * To generate custom stylesheet file
     *
     * @param Configuration $configuration
     */
    private function generateStylesheetFile(Configuration $configuration)
    {
        $defaultColors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'danger-light', 'light', 'dark', 'white'];
        $prefixes = ['alert' => 'alert', 'background' => 'bg', 'button' => 'btn', 'color' => 'txt'];
        $pushColors = [];

        foreach ($configuration->getColors() as $color) {
            $category = $color->getCategory();
            $colorSlug = !empty($prefixes[$category]) ? str_replace([$prefixes[$category] . '-', 'outline-'], '', $color->getSlug()) : NULL;
            if ($colorSlug && !in_array($colorSlug, $defaultColors) && $colorSlug !== 'link' && $color->getIsActive()) {
                $pushColors[] = $color;
            }
        }

        if ($pushColors) {

            $stylesheet = '';

            foreach ($pushColors as $pushColor) {
                $category = $pushColor->getCategory();
                if ($category === 'alert' || $category === 'background' || $category === 'button') {
                    $stylesheet .= '.' . $pushColor->getSlug() . '{background-color: ' . $pushColor->getColor() . '}';
                } elseif ($category === 'color') {
                    $stylesheet .= '.' . $pushColor->getSlug() . '{color: ' . $pushColor->getColor() . '}';
                }
            }

            if ($stylesheet) {

                $filesystem = new Filesystem();
                $dirname = $this->kernel->getProjectDir() . '/public/uploads/stylesheets/';
                $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
                $filename = 'custom-app-' . $configuration->getWebsite()->getUploadDirname() . '.css';

                if (!$filesystem->exists($dirname)) {
                    $filesystem->mkdir($dirname, 0777);
                }

                file_put_contents($dirname . $filename, $stylesheet);
            }
        }
    }

    /**
     * Synchronize Medias
     *
     * @param Configuration $configuration
     */
    private function synchronizeMedias(Configuration $configuration)
    {
        $defaultLocalesMedias = $this->getDefaultLocaleMedias($configuration);
        $defaultLocale = $configuration->getLocale();
        $repository = $this->entityManager->getRepository(MediaRelation::class);
        $flush = false;
        $this->setManifest($configuration);

        foreach (self::ADD_CUSTOM_DEFAULT_MEDIAS as $category) {
            $existing = false;
            foreach ($defaultLocalesMedias as $defaultLocaleMedia) {
                if ($defaultLocaleMedia->getCategory() === $category) {
                    $existing = true;
                }
            }
            if (!$existing) {
                foreach ($configuration->getAllLocales() as $locale) {
                    $this->addMedia($locale, $configuration, NULL, $category);
                    $this->entityManager->persist($configuration);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($configuration);
                }
            }
        }

        foreach ($defaultLocalesMedias as $mediaRelation) {
            /** @var MediaRelation $mediaRelation */
            foreach ($configuration->getAllLocales() as $locale) {
                if ($locale !== $defaultLocale && !in_array($mediaRelation->getCategory(), self::ADD_CUSTOM_DEFAULT_MEDIAS)) {
                    $existing = $repository->findDefaultLocaleCategory($configuration->getWebsite(), $mediaRelation->getCategory(), $locale);
                    if (!$existing) {
                        $this->addMedia($locale, $configuration, $mediaRelation);
                        $flush = true;
                    }
                }
            }
        }

        if ($flush) {
            $this->entityManager->persist($configuration);
            $this->entityManager->flush();
        }
    }

    /**
     * To generate manifest file
     *
     * @param Configuration $configuration
     */
    private function setManifest(Configuration $configuration)
    {
        $protocol = $this->request->isSecure() ? 'https://' : 'http://';
        $locale = $configuration->getLocale();
        $filesystem = new Filesystem();
        $website = $configuration->getWebsite();
        $domains = $this->entityManager->getRepository(Domain::class)->findBy(['configuration' => $configuration, 'locale' => $locale, 'hasDefault' => true]);
        $domain = !empty($domains[0]) ? $domains[0]->getName() : $this->request->getSchemeAndHttpHost();
        $information = $website instanceof Website ? $this->i18nRuntime->i18n($website->getInformation(), $locale) : NULL;
        $name = $information instanceof i18n ? $information->getTitle() : NULL;
        $logos = $website instanceof Website ? $this->mediaRuntime->logos($website, $locale) : [];
        $theme = $website instanceof Website ? $this->colorRuntime->color('favicon', $website, 'webmanifest-theme') : NULL;
        $background = $website instanceof Website ? $this->colorRuntime->color('favicon', $website, 'webmanifest-background') : NULL;

        $icons = [];
        $uploadDirname = $website->getUploadDirname();
        $publicDir = $this->kernel->getProjectDir() . '/public';
        $files = ['android-chrome-144x144' => '144x144', 'android-chrome-192x192' => '192x192', 'android-chrome-512x512' => '512x512'];
        foreach ($files as $fileName => $size) {
            if (!empty($logos[$fileName])) {
                $fileDirname = $publicDir . $logos[$fileName];
                if ($filesystem->exists($fileDirname)) {
                    $file = $file = new File(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname));
                    $icons[] = [
                        "src" => "/uploads/" . $uploadDirname . "/" . $fileName . "." . $file->getExtension(),
                        "sizes" => $size,
                        "type" => "image/" . $file->getExtension()
                    ];
                }
            }
        }

        $response = new JsonResponse([
            'name' => $name,
            'short_name' => $name,
            'description' => $name,
            'icons' => $icons,
            'start_url' => $protocol . $domain,
            'display' => 'standalone', /** or fullscreen */
            'theme_color' => $theme instanceof Color && $theme->getIsActive() ? $theme->getColor() : '#ffffff',
            'background_color' => $background instanceof Color && $background->getIsActive() ? $background->getColor() : '#ffffff',
        ]);
        $response->setEncodingOptions(16);
        $dirname = $this->kernel->getProjectDir() . '/public/uploads/' . $website->getUploadDirname() . '/manifest.webmanifest.json';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $filesystem->dumpFile($dirname, $response->getContent());

        $mediaRelations = $this->entityManager->getRepository(MediaRelation::class)->findBy(['category' => 'webmanifest']);
        foreach ($mediaRelations as $mediaRelation) {
            $media = $mediaRelation->getMedia();
            if($media instanceof Media && $media->getWebsite()->getId() === $website->getId()) {
                $media->setFilename('manifest.webmanifest.json');
                $media->setName('manifest.webmanifest');
                $media->setExtension('json');
                $this->entityManager->persist($media);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * Synchronize SocialNetwork
     *
     * @param Configuration $configuration
     */
    private function synchronizeNetworks(Configuration $configuration)
    {
        $flush = false;
        $information = $configuration->getWebsite()->getInformation();
        $existingLocales = [];
        foreach ($information->getSocialNetworks() as $socialNetwork) {
            $existingLocales[] = $socialNetwork->getLocale();
        }

        foreach ($configuration->getAllLocales() as $locale) {
            if (!in_array($locale, $existingLocales)) {
                $socialNetwork = new SocialNetwork();
                $socialNetwork->setLocale($locale);
                $information->addSocialNetwork($socialNetwork);
                $flush = true;
            }
        }

        if ($flush) {
            $this->entityManager->persist($configuration);
            $this->entityManager->flush();
        }
    }

    /**
     * Get default medias
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getDefaultLocaleMedias(Configuration $configuration): array
    {
        $medias = [];
        $defaultLocale = $configuration->getLocale();
        foreach ($configuration->getMediaRelations() as $mediaRelation) {
            if ($mediaRelation->getLocale() === $defaultLocale) {
                $medias[] = $mediaRelation;
            }
        }

        return $medias;
    }

    /**
     * Add Media
     *
     * @param string $locale
     * @param Configuration $configuration
     * @param MediaRelation|null $defaultRelation
     * @param string|null $category
     */
    private function addMedia(string $locale, Configuration $configuration, MediaRelation $defaultRelation = NULL, string $category = NULL)
    {
        $media = $defaultRelation instanceof MediaRelation ? $defaultRelation->getMedia() : new Media();
        $category = $defaultRelation instanceof MediaRelation ? $defaultRelation->getCategory() : $category;
        $media->setWebsite($configuration->getWebsite());
        $media->setCategory($category);

        $mediaRelation = new MediaRelation();
        $mediaRelation->setLocale($locale);
        $mediaRelation->setCategory($category);
        $mediaRelation->setMedia($media);

        $configuration->addMediaRelation($mediaRelation);
    }
}