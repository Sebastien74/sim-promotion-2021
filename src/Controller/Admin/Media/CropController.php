<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Media\Media;
use App\Entity\Media\Thumb;
use App\Entity\Media\ThumbConfiguration;
use App\Form\Manager\Core\GlobalManager;
use App\Form\Type\Media\ThumbType;
use App\Repository\Media\ThumbRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CropController
 *
 * Media crop management
 *
 * @Route("/admin-%security_token%/{website}/medias/cropper", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CropController extends AdminController
{
    /**
     * Crop Media Index
     *
     * @Route("/index/{media}", name="admin_media_crop")
     *
     * @param Website $website
     * @param Media $media
     * @return Response
     * @throws Exception
     */
    public function cropIndex(Website $website, Media $media)
    {
        return $this->cache('admin/page/media/crop.html.twig', [
            'media' => $media,
            'thumbs' => $this->getThumbs($website, $media)
        ]);
    }

    /**
     * Crop Media Index
     *
     * @Route("/cropper/{media}/{thumbConfiguration}", name="admin_media_cropper")
     *
     * @param Request $request
     * @param Media $media
     * @param ThumbRepository $thumbRepository
     * @param ThumbConfiguration $thumbConfiguration
     * @return Response
     * @throws Exception
     */
    public function cropper(
        Request $request,
        Media $media,
        ThumbRepository $thumbRepository,
        ThumbConfiguration $thumbConfiguration)
    {
        $mediaThumb = $thumbRepository->findOneBy([
            'media' => $media,
            'configuration' => $thumbConfiguration
        ]);

        if (!$mediaThumb) {
            $mediaThumb = new Thumb();
            $mediaThumb->setMedia($media)
                ->setConfiguration($thumbConfiguration)
                ->setWidth($thumbConfiguration->getWidth())
                ->setHeight($thumbConfiguration->getHeight());
            $media->addThumb($mediaThumb);
        }

        $form = $this->createForm(ThumbType::class, $mediaThumb);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaThumb = $form->getData();
            $this->entityManager->persist($mediaThumb);
            $this->entityManager->flush();
            return $this->redirect($request->headers->get('referer'));
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            /** @var GlobalManager $manager */
            $manager = $this->subscriber->get(GlobalManager::class);
            $manager->invalid($form);
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->cache('admin/page/media/cropper.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
            'isInfinite' => $thumbConfiguration->getWidth() < 1 && $thumbConfiguration->getHeight() < 1,
            'thumb' => $mediaThumb,
            'configuration' => $thumbConfiguration
        ]);
    }

    /**
     * Get Media ThumbConfiguration
     *
     * @param Website $website
     * @param Media $media
     * @return ThumbConfiguration[]
     */
    private function getThumbs(Website $website, Media $media)
    {
        $namespaces = [];
        $excludes = [Media::class, Configuration::class];
        $meta = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($meta as $metaEntity) {

            $associationsMapping = $this->entityManager->getClassMetadata($metaEntity->getName())->getAssociationMappings();
            $haveMedias = !empty($associationsMapping['mediaRelation']) || !empty($associationsMapping['mediaRelations']);
            $repository = $haveMedias ? $this->entityManager->getRepository($metaEntity->getName()) : NULL;

            if ($haveMedias && !in_array($metaEntity->getName(), $excludes)) {

                $relation = !empty($associationsMapping['mediaRelation']) ? 'mediaRelation' : 'mediaRelations';
                $existing = $repository->createQueryBuilder('e')->select('e')
                    ->leftJoin('e.' . $relation, 'mr')
                    ->andWhere('mr.media = :media')
                    ->setParameter('media', $media)
                    ->addSelect('mr')
                    ->getQuery()
                    ->getResult();

                if ($existing) {
                    foreach ($existing as $item) {
                        $namespaces[] = [
                            'entity' => $item,
                            'classname' => $metaEntity->getName()
                        ];
                    }
                }
            }
        }

        return $this->entityManager->getRepository(ThumbConfiguration::class)
            ->findByNamespaces($namespaces, $website->getConfiguration());
    }
}