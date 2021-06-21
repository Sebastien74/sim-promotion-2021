<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\Logo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LogoController
 *
 * Security Company Logo management
 *
 * @Route("/admin-%security_token%/{website}/security/compagnies/logo", schemes={"%protocol%"})
 * @IsGranted("ROLE_USERS")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogoController extends AdminController
{
    /**
     * Delete Logo
     *
     * @Route("/delete/{logo}", methods={"GET"}, name="admin_logo_delete")
     * @IsGranted("ROLE_DELETE")
     *
     * @param Logo $logo
     * @return JsonResponse
     */
    public function deleteLogo(Logo $logo)
    {
        $dirname = $this->kernel->getProjectDir() . '/public/' . $logo->getDirname();
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $filesystem = new Filesystem();

        if ($logo->getDirname() && $filesystem->exists($dirname) && !is_dir($dirname)) {
            $filesystem->remove($dirname);
        }

        $logo->setFilename(NULL);
        $logo->setDirname(NULL);

        $this->entityManager->persist($logo);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}