<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use App\Service\Core\Uploader;
use Doctrine\ORM\EntityManagerInterface;

/**
 * UploadedFileFixture
 *
 * Uploaded File Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property Uploader $uploader
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UploadedFileFixture
{
    private $entityManager;
    private $uploader;

    /**
     * UploadedFileFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Uploader $uploader
     */
    public function __construct(EntityManagerInterface $entityManager, Uploader $uploader)
    {
        $this->entityManager = $entityManager;
        $this->uploader = $uploader;
    }

    /**
     * Upload file
     *
     * @param Website $website
     * @param string $path
     * @param string $locale
     * @param mixed|null $entity
     * @param string|null $category
     * @param string|null $relationCategory
     * @param User|null $user
     * @return Media|null
     */
    public function uploadedFile(
        Website $website,
        string $path,
        string $locale,
        $entity = NULL,
        string $category = NULL,
        string $relationCategory = NULL,
        User $user = NULL): ?Media
    {
        $uploadedFile = $this->uploader->pathToUploadedFile($path);

        if ($uploadedFile) {

            $this->uploader->upload($uploadedFile, $website);

            $media = new Media();
            $media->setWebsite($website);
            $media->setFilename($this->uploader->getFilename());
            $media->setName($this->uploader->getName());
            $media->setExtension($this->uploader->getExtension());
            $media->setCategory($category);

            $i18n = new i18n();
            $i18n->setLocale($locale);
            $i18n->setWebsite($website);
            $i18n->setTitle($this->uploader->getName());
            $media->addI18n($i18n);

            $relation = new MediaRelation();

            if ($entity) {

                $relation->setLocale($locale);
                $relation->setMedia($media);
                $relation->setCategory($relationCategory);

                $entity->addMediaRelation($relation);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            if ($user) {
                $media->setCreatedBy($user);
                $i18n->setCreatedBy($user);
            }

            if ($user && $entity) {
                $relation->setCreatedBy($user);
            }

            return $media;
        }

        return NULL;
    }

    /**
     * Generate Folder
     *
     * @param Website $website
     * @param string $adminName
     * @param string $slug
     * @param Folder|null $parentFolder
     * @param User|null $user
     * @param bool $isWebmaster
     * @return Folder
     */
    public function generateFolder(
        Website $website,
        string $adminName,
        string $slug,
        Folder $parentFolder = NULL,
        User $user = NULL,
        bool $isWebmaster = true): Folder
    {
        $folder = new Folder();
        $folder->setAdminName($adminName);
        $folder->setWebsite($website);
        $folder->setWebmaster($isWebmaster);
        $folder->setSlug($slug);
        $folder->setDeletable(!$isWebmaster);

        if ($user) {
            $folder->setCreatedBy($user);
        }

        $position = count($this->entityManager->getRepository(Folder::class)->findBy([
                'website' => $website,
                'parent' => $parentFolder
            ])) + 1;

        $folder->setPosition($position);

        if ($parentFolder) {
            $folder->setLevel($parentFolder->getLevel() + 1);
            $folder->setParent($parentFolder);
            $this->entityManager->refresh($parentFolder);
        }

        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        return $folder;
    }
}