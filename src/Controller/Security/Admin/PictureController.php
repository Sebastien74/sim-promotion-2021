<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\Picture;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PictureController
 *
 * Security User Picture management
 *
 * @Route("/admin-%security_token%/{website}/security/users/picture", schemes={"%protocol%"})
 * @IsGranted("ROLE_USERS")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PictureController extends AdminController
{
    /**
     * Delete Picture
     *
     * @Route("/delete/{picture}", methods={"GET"}, name="admin_userpicture_delete")
     * @IsGranted("ROLE_DELETE")
     *
     * @param Picture $picture
     * @return JsonResponse
     */
    public function deletePicture(Picture $picture)
    {
        $dirname = $this->kernel->getProjectDir() . '/public/' . $picture->getDirname();
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $filesystem = new Filesystem();

        if ($picture->getDirname() && $filesystem->exists($dirname) && !is_dir($dirname)) {
            $filesystem->remove($dirname);
        }

        $picture->setFilename(NULL);
        $picture->setDirname(NULL);

        $this->entityManager->persist($picture);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}