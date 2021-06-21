<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Configuration;
use App\Entity\Core\Icon;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * IconManager
 *
 * Manage admin Configuration form
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property string $baseDirname
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconManager
{
    private $entityManager;
    private $kernel;
    private $baseDirname;

    /**
     * IconManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->baseDirname = '/medias/icons/app';
    }

    /**
     * Execute
     *
     * @param Website $website
     * @param Form $form
     * @return JsonResponse
     */
    public function execute(Website $website, Form $form)
    {
        if($form->isValid()) {

            $libraryDirname = $this->kernel->getProjectDir() . '/public' . $this->baseDirname;
            $libraryDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $libraryDirname);
            $filesystem = new Filesystem();
            $icons = $form->get('uploadedFile')->getData();
            $configuration = $website->getConfiguration();

            foreach ($icons as $icon) {

                if ($icon instanceof UploadedFile) {

                    $extension = $icon->guessExtension();
                    $originalFilename = pathinfo($icon->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = Urlizer::urlize($originalFilename);
                    $existing = $filesystem->exists($libraryDirname . '/' . $safeFilename . '.' . $extension);
                    $newFilename = $existing ? $safeFilename . '-' . uniqid() . '.' . $extension : $safeFilename . '.' . $extension;

                    try {
                        $icon->move($libraryDirname, $newFilename);
                    } catch (FileException $exception) {
                        return new JsonResponse(['success' => false, 'errors' => $exception->getMessage()]);
                    }

                    $this->addIcon($newFilename, $this->baseDirname . '/' . $newFilename, $configuration);
                }
            }

            return new JsonResponse(['success' => true, 'form' => $form]);

        } elseif (!$form->isValid()) {

            $errors = '';
            foreach ($form->get('uploadedFile') as $error) {
                $errors .= $error->getMessage() . '</br>';
            }
            return new JsonResponse(['success' => false, 'errors' => rtrim($errors, '</br>')]);
        }
    }

    /**
     * Add Icon entity
     *
     * @param string $newFilename
     * @param string $path
     * @param Configuration $configuration
     */
    public function addIcon(string $newFilename, string $path, Configuration $configuration)
    {
        $icon = new Icon();
        $icon->setFilename($newFilename);
        $icon->setPath($path);
        $icon->setConfiguration($configuration);

        $filenameMatches = explode('.', $icon->getFilename());
        $icon->setExtension(end($filenameMatches));

        if (preg_match('/icons\/flags/', $icon->getPath())) {
            $icon->setLocale($filenameMatches[0]);
        }

        $this->entityManager->persist($icon);
        $this->entityManager->flush();
    }

    /**
     * Add Icon entity
     *
     * @param string $path
     * @param Configuration $configuration
     */
    public function remove(string $path, Configuration $configuration)
    {
        $icon = $this->entityManager->getRepository(Icon::class)->findBy([
            'configuration' => $configuration,
            'path' => $path
        ]);

        if ($icon) {
            $this->entityManager->remove($icon[0]);
            $this->entityManager->flush();
        }
    }
}