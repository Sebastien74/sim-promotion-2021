<?php

namespace App\Form\Manager\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Repository\Media\MediaRelationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ModalLibraryManager
 *
 * Manage admin Media by modal Library form
 *
 * @property EntityManagerInterface $entityManager
 * @property MediaRelationRepository $mediaRelationRepository
 * @property mixed $repository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModalLibraryManager
{
    private $entityManager;
    private $mediaRelationRepository;
    private $repository;

    /**
     * ModalLibraryManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add MediaRelation
     *
     * @param Website $website
     * @param Media $media
     * @param string|NULL $options
     */
    public function add(Website $website, Media $media, string $options = NULL)
    {
        $options = (object)json_decode($options);
        $this->mediaRelationRepository = $this->entityManager->getRepository(MediaRelation::class);
        $this->repository = $this->entityManager->getRepository(urldecode($options->classname));
        $entity = $this->repository->find($options->entityId);

        if ($options->type === 'single') {
            $this->addSingle($entity, $media, $options);
        } elseif ($options->type === 'multiple') {
            $this->addMultiple($website, $entity, $media);
        }

        $this->entityManager->flush();
    }

    /**
     * Single
     *
     * @param $entity
     * @param Media $media
     * @param null $options
     */
    private function addSingle($entity, Media $media, $options = NULL)
    {
        if(property_exists($options, 'mediaRelationId')) {

            $identifier = method_exists($entity, 'getMediaRelations') ? 'mediaRelations' : 'mediaRelation';
            $mediaRelation = $this->mediaRelationRepository->find($options->mediaRelationId);
            $mediaRelation->setMedia($media);

            if (!$entity instanceof MediaRelation) {

                $existing = $this->repository->createQueryBuilder('e')->select('e')
                    ->leftJoin('e.' . $identifier, 'mr')
                    ->andWhere('e.id = :id')
                    ->andWhere('mr.media = :media')
                    ->setParameter('media', $media)
                    ->setParameter('id', $entity->getId())
                    ->addSelect('mr')
                    ->getQuery()
                    ->getResult();

                if (!$existing) {
                    $setter = method_exists($entity, 'getMediaRelations') ? 'addMediaRelation' : 'setMediaRelation';
                    $entity->$setter($mediaRelation);
                    $this->entityManager->persist($entity);
                }
            } else {
                $this->entityManager->persist($mediaRelation);
            }
        }
        if(property_exists($options, 'mediaId') && $entity instanceof Media) {

            $videoFormats = ['webm', 'ogv', 'mp4'];
            if(!in_array($entity->getScreen(), $videoFormats)) {
                $entity->setExtension($media->getExtension());
            }

            $entity->setName($media->getName());
            $entity->setFilename($media->getFilename());
            $entity->setCopyright($media->getCopyright());
            $entity->setNotContractual($media->getNotContractual());
            $this->entityManager->persist($entity);
        }
    }

    /**
     * Multiple
     *
     * @param Website $website
     * @param $entity
     * @param Media $media
     */
    private function addMultiple(Website $website, $entity, Media $media)
    {
        $locale = $website->getConfiguration()->getLocale();
        $localeEntity = $this->repository->createQueryBuilder('e')->select('e')
            ->leftJoin('e.mediaRelations', 'mr')
            ->andWhere('e.id = :id')
            ->andWhere('mr.locale = :locale')
            ->setParameter('id', $entity->getId())
            ->setParameter('locale', $locale)
            ->addSelect('mr')
            ->getQuery()
            ->getOneOrNullResult();

        $position = $localeEntity ? count($localeEntity->getMediaRelations()) + 1 : 1;
        $mediaRelation = new MediaRelation();
        $mediaRelation->setPosition($position);
        $mediaRelation->setLocale($locale);
        $mediaRelation->setMedia($media);

        $entity->addMediaRelation($mediaRelation);
        $this->entityManager->persist($entity);
    }
}