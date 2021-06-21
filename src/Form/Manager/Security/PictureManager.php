<?php

namespace App\Form\Manager\Security;

use App\Entity\Security\Picture;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * PictureManager
 *
 * Manage User Picture
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PictureManager
{
    private $entityManager;
    private $kernel;

    /**
     * PictureManager constructor.
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
     * Set User Picture
     *
     * @param User|UserFront $user
     * @param Form $form
     */
    public function execute($user, Form $form)
    {
        /** @var UploadedFile $file */
        $file = $form->get('file')->getData();

        if ($file instanceof UploadedFile) {

            $filesystem = new Filesystem();
            $picture = $user->getPicture() ? $user->getPicture() : new Picture();
            $extension = $file->guessExtension();
            $filename = Urlizer::urlize(str_replace('.' . $extension, '', $file->getClientOriginalName())) . '-' . md5(uniqid()) . '.' . $extension;
            $userDirname = $user instanceof User ? 'users' : 'users-front';
            $baseDirname = '/uploads/' . $userDirname . '/' . $user->getSecretKey() . '/picture/';
            $baseDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirname);
            $publicDirname = $this->kernel->getProjectDir() . '/public/';
            $publicDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $publicDirname);
            $dirname = $publicDirname . $baseDirname;

            if ($picture->getDirname() && $filesystem->exists($publicDirname . $picture->getDirname()) && !is_dir($publicDirname . $picture->getDirname())) {
                $filesystem->remove($publicDirname . $picture->getDirname());
            }

            $picture->setFilename($filename);
            $picture->setDirname($baseDirname . $filename);

            if (!$picture->getId()) {
                $picture->setUserFront($user);
                $user->setPicture($picture);
            }

            $file->move($dirname, $filename);
        }
    }
}