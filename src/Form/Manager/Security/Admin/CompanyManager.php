<?php

namespace App\Form\Manager\Security\Admin;

use App\Entity\Core\Website;
use App\Entity\Security\Company;
use App\Entity\Security\Logo;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CompanyManager
 *
 * Manage Company in admin
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CompanyManager
{
    private $entityManager;
    private $kernel;

    /**
     * CompanyManager constructor.
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
     * @prePersist
     *
     * @param Company $company
     * @param Website $website
     */
    public function prePersist(Company $company, Website $website)
    {

    }

    /**
     * @preUpdate
     *
     * @param Company $company
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(Company $company, Website $website, array $interface, Form $form)
    {
        $this->setLogo($company, $form);

        $address = $company->getAddress();
        if (!$address->getId()) {
            $address->setCompany($company);
            $this->entityManager->persist($address);
        }

        $this->entityManager->persist($company);
    }

    /**
     * Set Company Logo
     *
     * @param Company $company
     * @param Form $form
     */
    private function setLogo(Company $company, Form $form)
    {
        /** @var UploadedFile $file */
        $file = $form->get('file')->getData();

        if ($file instanceof UploadedFile) {

            /** @var Logo $logo */
            $logo = $company->getLogo() ? $company->getLogo() : new Logo();
            $filesystem = new Filesystem();
            $extension = $file->guessExtension();
            $filename = Urlizer::urlize(str_replace('.' . $extension, '', $file->getClientOriginalName())) . '-' . md5(uniqid()) . '.' . $extension;
            $baseDirname = '/uploads/companies/' . $company->getSecretKey() . '/logo/';
            $baseDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDirname);
            $publicDirname = $this->kernel->getProjectDir() . '/public/';
            $publicDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $publicDirname);
            $dirname = $publicDirname . $baseDirname;

            if ($logo->getDirname() && $filesystem->exists($publicDirname . $logo->getDirname()) && !is_dir($publicDirname . $logo->getDirname())) {
                $filesystem->remove($publicDirname . $logo->getDirname());
            }

            $logo->setFilename($filename);
            $logo->setDirname($baseDirname . $filename);

            if (!$logo->getId()) {
                $logo->setCompany($company);
                $company->setLogo($logo);
            }

            $file->move($dirname, $filename);
        }
    }
}