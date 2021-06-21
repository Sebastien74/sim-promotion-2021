<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MapFixture
 *
 * Map Fixture management
 *
 * @property array MARKERS_COLORS
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property UploadedFileFixture $uploader
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MapFixture
{
    private const MARKERS_COLORS = [
        'blue', 'green', 'grey', 'orange', 'pink', 'yellow'
    ];

    private $entityManager;
    private $translator;
    private $uploader;
    private $kernel;

    /**
     * MapFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param UploadedFileFixture $uploader
     * @param KernelInterface $kernel
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        UploadedFileFixture $uploader,
        KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->uploader = $uploader;
        $this->kernel = $kernel;
    }

    /**
     * Add Map
     *
     * @param Folder $webmasterFolder
     * @param Website $website
     * @param User|NULL $user
     */
    public function add(Folder $webmasterFolder, Website $website, User $user = NULL)
    {
        $this->addMarkers($webmasterFolder, $website, $user);
    }

    private function addMarkers(Folder $webmasterFolder, Website $website, User $user = NULL)
    {
        $mediaFolder = $this->uploader->generateFolder($website, 'Map', 'map', $webmasterFolder, $user);

        foreach (self::MARKERS_COLORS as $color) {
            $path = $this->kernel->getProjectDir() . '/assets/medias/images/default/map/marker-' . $color . '.svg';
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            $media = $this->uploader->uploadedFile($website, $path, $website->getConfiguration()->getLocale(), NULL, "map", NULL, $user);
            $media->setFolder($mediaFolder);
            $this->entityManager->persist($media);
            $this->entityManager->flush();
        }
    }
}