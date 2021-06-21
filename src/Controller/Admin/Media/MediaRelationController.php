<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Form\Manager\Media\MediaManager;
use App\Form\Widget\MediaRelationType;
use App\Repository\Media\MediaRelationRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MediaRelationController
 *
 * Media MediaRelation management
 *
 * @Route("/admin-%security_token%/{website}/medias/relations", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @property MediaRelation $class
 * @property MediaRelationType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelationController extends AdminController
{
    protected $class = MediaRelation::class;
    protected $formType = MediaRelationType::class;

    /**
     * Reset Media null on MediaRelation (Use on dropify delete action)
     *
     * @IsGranted("ROLE_DELETE")
     *
     * @Route("/reset-media/{mediaRelation}", methods={"GET"}, name="admin_mediarelation_reset_media", options={"expose"=true})
     * @param MediaRelation|null $mediaRelation
     * @return JsonResponse
     */
    public function resetMedia(MediaRelation $mediaRelation = NULL)
    {
        if ($mediaRelation) {

            $media = $mediaRelation->getMedia();

            if ($media instanceof Media && $media->getScreen() === 'poster') {
                $media->setName(NULL);
                $media->setFilename(NULL);
                $media->setExtension(NULL);
                $media->setCopyright(NULL);
                $this->entityManager->persist($media);
            } else {
                $mediaRelation->setMedia(NULL);
                $this->entityManager->persist($mediaRelation);
            }

            $this->entityManager->flush();
        }

        return new JsonResponse(["success" => true]);
    }

    /**
     * Edit Locales MediaRelation
     *
     * @Route("/edit/locales/{mediaRelation}/{entityNamespace}/{entityId}", methods={"GET", "POST"}, name="admin_mediarelations_edit", options={"expose"=true})
     * @IsGranted("ROLE_EDIT")
     *
     * @param Request $request
     * @param MediaManager $mediaManager
     * @param MediaRelation $mediaRelation
     * @param string $entityNamespace
     * @param int $entityId
     * @return Response
     * @throws Exception
     */
    public function relationsEdit(
        Request $request,
        MediaManager $mediaManager,
        MediaRelation $mediaRelation,
        string $entityNamespace,
        int $entityId)
    {

        $this->disableProfiler();

        $website = $this->getWebsite($request);
        $classname = urldecode($entityNamespace);
        $interface = $this->getInterface($classname);
        $repository = $this->entityManager->getRepository($classname);

        $entity = $repository->find($entityId);
        if (!$entity) {
            throw $this->createNotFoundException($this->translator->trans("Ce média n'existe pas !!", [], 'front'));
        }

        $mediaManager->synchronizeLocales($website, $interface, $entity, $mediaRelation);

        $entityLocaleRelation = $repository->createQueryBuilder('e')->select('e')
            ->leftJoin('e.mediaRelations', 'm')
            ->andWhere('m.position = :position')
            ->andWhere('e.id = :id')
            ->setParameter('position', $mediaRelation->getPosition())
            ->setParameter('id', $entity->getId())
            ->addSelect('m')
            ->orderBy('m.locale', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();

        return new JsonResponse(['html' => $this->cache('admin/core/form/media-relations-multi.html.twig', [
            'mediaRelations' => $entityLocaleRelation ? $entityLocaleRelation->getMediaRelations() : [NULL]
        ], $request)]);
    }

    /**
     * Edit MediaRelation
     *
     * @Route("/edit/{mediarelation}", methods={"GET", "POST"}, name="admin_mediarelation_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->entity = $this->entityManager->getRepository(MediaRelation::class)->find($request->get('mediarelation'));

        if (!$this->entity) {
            throw $this->createNotFoundException($this->translator->trans("Ce média n'existe pas !!", [], 'front'));
        }

        $this->template = 'admin/core/form/media-relation-full.html.twig';
        $this->formOptions = ['copyright' => true, 'categories' => true, 'fields' => [
            'submit',
            'i18n' => ['title' => 'col-md-10', 'subTitle' => 'col-md-9', 'pictogram' => 'col-md-3', 'body', 'introduction', 'targetLink', 'targetLabel', 'targetPage', 'newTab' => 'col-md-4', 'alignment' => 'col-12']
        ], 'form_name' => 'media_relation_' . $this->entity->getId()];

        return parent::edit($request);
    }

    /**
     * Set MediaRelation position
     *
     * @Route("/position/{mediaRelation}/{position}", methods={"GET"}, name="admin_mediarelation_position", options={"expose"=true})
     * @IsGranted("ROLE_EDIT")
     *
     * @param MediaRelation $mediaRelation
     * @param int $position
     * @return JsonResponse
     */
    public function relationPosition(MediaRelation $mediaRelation, int $position)
    {
        $mediaRelation->setPosition($position);
        $this->entityManager->persist($mediaRelation);
        $this->entityManager->flush();

        return new JsonResponse(["success" => true]);
    }

    /**
     * Delete MediaRelation
     *
     * @Route("/relation/delete/{mediaRelation}/{entityNamespace}/{entityId}", methods={"DELETE"}, name="admin_mediarelation_delete")
     * @IsGranted("ROLE_DELETE")
     *
     * @param MediaRelationRepository $mediaRelationRepository
     * @param MediaRelation $mediaRelation
     * @param string $entityNamespace
     * @param int $entityId
     * @return JsonResponse
     */
    public function relationDelete(MediaRelationRepository $mediaRelationRepository, MediaRelation $mediaRelation, string $entityNamespace, int $entityId)
    {
        $positionToRemove = $mediaRelation->getPosition();
        $localesRelations = $this->getAllLocalesRelations($mediaRelationRepository, $entityNamespace, $entityId);

        if ($localesRelations) {

            foreach ($localesRelations as $relation) {
                if ($relation->getPosition() === $positionToRemove) {
                    $this->entityManager->remove($relation);
                } elseif ($relation->getPosition() > $positionToRemove) {
                    $relation->setPosition($relation->getPosition() - 1);
                }
            }

            $this->entityManager->flush();
        }

        return new JsonResponse(["success" => true]);
    }

    /**
     * Get locales relations
     *
     * @param MediaRelationRepository $mediaRelationRepository
     * @param string $entityNamespace
     * @param int $entityId
     * @return MediaRelation[]
     */
    private function getAllLocalesRelations(MediaRelationRepository $mediaRelationRepository, string $entityNamespace, int $entityId)
    {
        $repository = $this->entityManager->getRepository(urldecode($entityNamespace));
        $entity = $repository->createQueryBuilder('e')->select('e')
            ->leftJoin('e.mediaRelations', 'm')
            ->andWhere('e.id = :id')
            ->setParameter('id', $entityId)
            ->addSelect('m')
            ->getQuery()
            ->getOneOrNullResult();

        return $entity ? $entity->getMediaRelations() : [];
    }
}