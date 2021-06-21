<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Layout;
use Doctrine\ORM\PersistentCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LayoutController
 *
 * Layout management
 *
 * @Route("/admin-%security_token%/{website}/layouts", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Layout $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutController extends AdminController
{
    protected $class = Layout::class;

    /**
     * Index Layout
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_layout_index")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Delete Layout
     *
     * @Route("/delete/{layout}", methods={"DELETE"}, name="admin_layout_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Layout Layout
     *
     * @Route("/layout/{layout}", methods={"GET"}, name="admin_layout_layout")
     *
     * {@inheritdoc}
     */
    public function layout(Request $request)
    {
        $mappedEntityInfos = $this->getMappedEntityInfos($request);

        if (!$mappedEntityInfos) {
            throw $this->createNotFoundException($this->translator->trans("L'entité de cette mise en page a été supprimé.", [], 'front'));
        }

        return $this->redirectToRoute('admin_' . $mappedEntityInfos->interface['name'] . '_layout', [
            'website' => $this->getWebsite($request)->getId(),
            $mappedEntityInfos->interface['name'] => $mappedEntityInfos->entity->getId()
        ]);
    }

    /**
     * Reset Layout
     *
     * @Route("/reset/{layout}", methods={"DELETE"}, name="admin_layout_reset")
     * @IsGranted("ROLE_INTERNAL")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request)
    {
        $mappedEntityInfos = $this->getMappedEntityInfos($request);
        $setter = 'set' . ucfirst($mappedEntityInfos->interface['name']);
        $mappedEntity = $mappedEntityInfos->entity;

        /** @var Layout $layout */
        $layout = $mappedEntityInfos->layout;

        $newLayout = new Layout();
        $newLayout->setWebsite($this->getWebsite($request));
        $newLayout->setAdminName($layout->getAdminName());
        $newLayout->setPosition($layout->getPosition());
        $newLayout->$setter($mappedEntity);

        $mappedEntity->setLayout($newLayout);

        $this->entityManager->persist($newLayout);
        $this->entityManager->persist($mappedEntity);

        $this->entityManager->remove($layout);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Get Layout mapped entity
     *
     * @param Request $request
     * @return object
     */
    private function getMappedEntityInfos(Request $request)
    {
        $excludes = ['createdBy', 'updatedBy', 'zones', 'website'];
        $layout = $this->entityManager->getRepository(Layout::class)->find($request->get('layout'));
        $associationsMapping = $this->entityManager->getClassMetadata(Layout::class)->getAssociationMappings();

        foreach ($associationsMapping as $property => $properties) {

            $getter = 'get' . ucfirst($property);

            if (method_exists($layout, $getter)) {
                $mappedEntity = $layout->$getter();
                if (!in_array($property, $excludes) && !empty($mappedEntity)) {
                    $classname = NULL;
                    $entity = $mappedEntity;
                    if ($mappedEntity instanceof PersistentCollection and !$mappedEntity->isEmpty()) {
                        $classname = $this->entityManager->getClassMetadata(get_class($mappedEntity[0]))->getName();
                        $entity = $mappedEntity[0];
                    } elseif (!$mappedEntity instanceof PersistentCollection) {
                        $classname = $this->entityManager->getClassMetadata(get_class($mappedEntity))->getName();
                    }
                    return (object)[
                        'layout' => $layout,
                        'entity' => $entity,
                        'interface' => $this->getInterface($classname)
                    ];
                }
            }
        }
    }
}