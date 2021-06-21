<?php

namespace App\Twig\Content;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Repository\Core\ConfigurationRepository;
use App\Twig\Core\WebsiteRuntime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * MediaRuntime
 *
 * @property Request $request
 * @property KernelInterface $kernel
 * @property WebsiteRuntime $websiteExtension
 * @property EntityManagerInterface $entityManager
 * @property ConfigurationRepository $configurationRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $kernel;
    private $websiteExtension;
    private $entityManager;
    private $configurationRepository;

    /**
     * MediaRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param WebsiteRuntime $websiteExtension
     * @param EntityManagerInterface $entityManager
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(
        RequestStack $requestStack,
        KernelInterface $kernel,
        WebsiteRuntime $websiteExtension,
        EntityManagerInterface $entityManager,
        ConfigurationRepository $configurationRepository)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->kernel = $kernel;
        $this->websiteExtension = $websiteExtension;
        $this->entityManager = $entityManager;
        $this->configurationRepository = $configurationRepository;
    }

	/**
	 * Get locale logos
	 *
	 * @param mixed $website
	 * @param string|null $locale
	 * @return array|null
	 */
    public function logos($website = NULL, string $locale = NULL): ?array
    {
        if (!$website) {
            return NULL;
        }

        $logos = [];
        $socialLogos = [];
        $filesystem = new Filesystem();
        $uri = $this->request->getUri();
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $uploadDirname = $website instanceof Website ? $website->getUploadDirname() : $website['uploadDirname'];
        $configurationId = $website instanceof Website ? $website->getConfiguration()->getId() : $website['configuration']['id'];
        $socialNetworksCategories = ['facebook', 'google-plus', 'instagram', 'linkedin', 'pinterest', 'twitter', 'youtube'];
        $socialNetworks = !preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri) || preg_match('/\/preview\//', $uri)
            ? $this->websiteExtension->socialNetworks($website, $locale) : [];
        $mediaRelations = $this->entityManager->getRepository(Configuration::class)->findMediaRelations($configurationId);

        foreach ($mediaRelations as $mediaRelation) {
            $media = $mediaRelation->getMedia();
            $filename = $media->getFilename();
            $dirname = $media instanceof Media && $filename ? '/uploads/' . $uploadDirname . '/' . $filename : NULL;
            $appDirname = $this->kernel->getProjectDir() . '/public' . $dirname;
            $appDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $appDirname);
            $file = $media instanceof Media && $filesystem->exists($appDirname) && $filename ? $dirname : NULL;
            $category = $mediaRelation->getCategory();
            $mediaCategory = $media instanceof Media ? $media->getCategory() : NULL;
            $logos[$category] = $file;
            $logos['medias'][$category] = $media;
            $logos['mediaRelation'][$category] = $mediaRelation;
            if ($mediaCategory === 'social-network' && !empty($socialNetworks[$category])
                || in_array($mediaCategory, $socialNetworksCategories) && !empty($socialNetworks[$category])) {
                $arguments = [
                    'title' => ucfirst($category),
                    'link' => $socialNetworks[$category],
                    'dirname' => $file,
                    'icon' => $this->socialIcon($category)
                ];
                $socialLogos[$category] = $arguments;
                $logos['social-networks'] = $socialLogos;
                $logos[$category] = $arguments;
            } elseif ($category !== 'social-network') {
                $logos[$category] = $file;
                $logos['medias'][$category] = $media;
            }
        }

        ksort($logos);

        return $logos;
    }

    /**
     * Get social network icon by name
     *
     * @param string $name
     * @return string|null
     */
    public function socialIcon(string $name): ?string
    {
        $icons = [
            'facebook' => 'fab fa-facebook-f',
            'google-plus' => 'fab fa-google',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin-in',
            'pinterest' => 'fab fa-pinterest-p',
            'tripadvisor' => 'fab fa-tripadvisor',
            'twitter' => 'fab fa-twitter',
            'youtube' => 'fab fa-youtube',
        ];

        return !empty($icons[$name]) ? $icons[$name] : NULL;
    }

    /**
     * Check if entity have main Media
     *
     * @param mixed $entity
     * @return bool
     */
    public function haveMainMedia($entity): bool
    {
        if (method_exists($entity, 'getMediaRelations')) {

            $mediaRelations = $entity->getMediaRelations();

            foreach ($mediaRelations as $mediaRelation) {
                /** @var MediaRelation $mediaRelation */
                if ($mediaRelation->getIsMain() && $mediaRelation->getLocale() === $this->request->getLocale()) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Get main Media
     *
     * @param mixed $entity
     * @return null|MediaRelation
     */
    public function mainMedia($entity)
    {
        if (method_exists($entity, 'getMediaRelations')) {

            /** @var Collection $mediaRelations */
            $mediaRelations = $entity->getMediaRelations();
            $localeMediaRelation = NULL;

            foreach ($mediaRelations as $mediaRelation) {
                $asSameLocale = $mediaRelation->getLocale() === $this->request->getLocale();
                /** @var MediaRelation $mediaRelation */
                if ($mediaRelation->getIsMain() && $asSameLocale) {
                    return $mediaRelation;
                }
                if ($asSameLocale && !$localeMediaRelation) {
                    $localeMediaRelation = $mediaRelation;
                }
            }

            return $localeMediaRelation;
        }
    }

    /**
     * Get all entity MediaRelation[] group by position
     *
     * @param array $mediaRelations
     * @return array
     */
    public function mediasIdsByPosition($mediaRelations)
    {
        $groups = [];
        foreach ($mediaRelations as $mediaRelation) {
            /** @var MediaRelation $mediaRelation */
            $groups[$mediaRelation->getPosition()][] = $mediaRelation->getId();
        }

        return $groups;
    }

    /**
     * Get all entity MediaRelation[] group by categories
     *
     * @param array $mediaRelations
     * @return array
     */
    public function mediasByCategories($mediaRelations = NULL)
    {
        if (!$mediaRelations) {
            return [];
        }

        $groups = [];
        foreach ($mediaRelations as $mediaRelation) {
            /** @var MediaRelation $mediaRelation */
            $media = $mediaRelation->getMedia();
            foreach ($media->getCategories() as $category) {
                $groups[$category->getSlug()][$mediaRelation->getPosition()] = $mediaRelation;
                ksort($groups[$category->getSlug()]);
            }
        }

        ksort($groups);

        return $groups;
    }
}