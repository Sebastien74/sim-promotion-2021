<?php

namespace App\Service\Admin;

use App\Entity\Media\Media;
use App\Entity\Seo\Url;
use App\Helper\Admin\IndexHelper;
use App\Helper\Core\InterfaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * DeleteService
 *
 * Manage entity deletion
 *
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property InterfaceHelper $interfaceHelper
 * @property IndexHelper $indexHelper
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DeleteService
{
    private $request;
    private $entityManager;
    private $kernel;
    private $interfaceHelper;
    private $indexHelper;

    /**
     * DeleteService constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param InterfaceHelper $interfaceHelper
     * @param IndexHelper $indexHelper
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        InterfaceHelper $interfaceHelper,
        IndexHelper $indexHelper)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->interfaceHelper = $interfaceHelper;
        $this->indexHelper = $indexHelper;
    }

    /**
     * Remove an Entity
     *
     * @param string $classname
     * @param array|PersistentCollection $entities
     * @return bool
     */
    public function execute(string $classname, $entities = [])
    {
        $interface = $this->interfaceHelper->generate($classname);
        $repository = $this->entityManager->getRepository($classname);
        $entityToDelete = $repository->find($this->request->get($interface['name']));

        if ($entityToDelete instanceof Media && $entityToDelete->getScreen()) {
            $this->resetMedia($entityToDelete);
            return true;
        }

        if ($entityToDelete) {

            $this->indexHelper->execute($classname, $interface, 'all');

            if (!$entities) {

                $pagination = $this->indexHelper->getPagination();

                if ($pagination instanceof SlidingPagination) {
                    $entities = !empty($pagination) ? $pagination->getItems() : [];
                } elseif (is_array($pagination)) {
                    $entities = $pagination;
                } else {
                    $entities = [];
                }
            }

            $this->remove($entities, $entityToDelete);
        }
    }

    /**
     * Reset screen Media
     *
     * @param Media $media
     */
    private function resetMedia(Media $media)
    {
        /** Remove file */
        $fileDirname = $this->kernel->getProjectDir() . '/public/uploads/' . $media->getWebsite()->getUploadDirname() . '/' . $media->getFilename();
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
        $filesystem = new Filesystem();
        if ($filesystem->exists($fileDirname)) {
            $filesystem->remove($fileDirname);
        }

        /** Reset Media */
        $media->setName(NULL);
        $media->setFilename(NULL);
        $media->setExtension(NULL);

        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }

    /**
     * Remove Entity & set others Entities positions
     *
     * @param array|PersistentCollection $entities
     * @param mixed $entityToDelete
     */
    private function remove($entities, $entityToDelete)
    {
        if (is_object($entityToDelete) && method_exists($entityToDelete, 'getLevel')
            && method_exists($entityToDelete, 'getParent')) {
            $this->setLevels($entities, $entityToDelete);
        } else {
            $this->setPositions($entities, $entityToDelete);
        }

        if (is_object($entityToDelete) && method_exists($entityToDelete, 'getUrls')) {
            foreach ($entityToDelete->getUrls() as $url) {
                /** @var Url $url */
                $url->setIsIndex(false);
                $url->setIsOnline(false);
                $url->setHideInSitemap(true);
                $url->setIsArchived(true);
                $this->entityManager->persist($url);
            }
        } else {
            $this->entityManager->remove($entityToDelete);
        }

        $this->entityManager->flush();
    }

    /**
     * Set others levels
     *
     * @param array|PersistentCollection $entities
     * @param $entityToDelete
     */
    private function setLevels($entities, $entityToDelete)
    {
        foreach ($entities as $entity) {
            $haveParent = method_exists($entity, 'getParent') && method_exists($entityToDelete, 'getParent');
            if ($haveParent && $entity->getPosition() > $entityToDelete->getPosition()
                && $entity->getLevel() === $entityToDelete->getLevel()
                && $entity->getParent() === $entityToDelete->getParent()) {
                $entity->setPosition($entity->getPosition() - 1);
                $this->entityManager->persist($entity);
            }
        }
    }

    /**
     * Set others positions
     *
     * @param array|PersistentCollection $entities
     * @param $entityToDelete
     */
    private function setPositions($entities, $entityToDelete)
    {
        foreach ($entities as $entity) {
            if (method_exists($entity, 'getPosition')) {
                if ($entity->getPosition() > $entityToDelete->getPosition()) {
                    $entity->setPosition($entity->getPosition() - 1);
                    $this->entityManager->persist($entity);
                }
            }
        }
    }
}