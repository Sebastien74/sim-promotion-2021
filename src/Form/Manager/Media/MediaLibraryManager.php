<?php

namespace App\Form\Manager\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MediaLibraryManager
 *
 * Manage admin Media library form
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaLibraryManager
{
    private $entityManager;
    private $kernel;
    private $translator;

    /**
     * MediaLibraryManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->translator = $translator;
    }

    /**
     * @preUpdate
     *
     * @param Media $media
     * @param Website $website
     */
    public function preUpdate(Media $media, Website $website)
    {
        $this->renameFile($media, $website);

        foreach ($media->getMediaScreens() as $mediaScreen) {
            $this->renameFile($mediaScreen, $website);
        }
    }

    /**
     * Rename file
     *
     * @param Media $media
     * @param Website $website
     */
    private function renameFile(Media $media, Website $website)
    {
        $name = $media->getName();
        $filename = $media->getFilename();
        $extension = $media->getExtension();

        if (!empty($extension) && preg_match('/.' . $extension . '/', $filename)) {
            $extension = $extension;
        } else {
            $matches = explode('.', $filename);
            $extension = end($matches);
        }

        $originalName = str_replace('.' . $extension, '', $filename);

        if ($name !== $originalName && $filename) {

            $baseDirname = $this->kernel->getProjectDir() . '/public/uploads/' . $website->getUploadDirname() . '/';
            $baseDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirname);
            $filesystem = new Filesystem();
            $newFileDirname = $baseDirname . $name . '.' . $extension;

            if ($filesystem->exists($newFileDirname)) {
                $media->setName($originalName);
                $session = new Session();
                $session->getFlashBag()->add('error', $this->translator->trans("Un autre fichier porte déjà ce nom", [], 'admin') . ' : ' . $name . '.' . $extension);
            } else {
                $filesystem->rename($baseDirname . $filename, $newFileDirname);
                $media->setFilename($name . '.' . $extension);
            }
        }
    }
}