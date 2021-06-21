<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BooleanSwitcherController
 *
 * Boolean management
 *
 * @Route("/admin-%security_token%/{website}", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BooleanSwitcherController extends AdminController
{
    /**
     * To switch boolean
     *
     * @Route("/switch-boolean/{classname}/{entityId}/{property}", methods={"POST", "GET"}, name="admin_switch_boolean", schemes={"%protocol%"})
     * @IsGranted("ROLE_EDIT")
     *
     * @param Request $request
     * @param Website $website
     * @param string $classname
     * @param int $entityId
     * @param string $property
     * @return JsonResponse
     */
    public function switchBoolean(Request $request, Website $website, string $classname, int $entityId, string $property)
    {
        $status = $request->get('status') === 'true';
        $repository = $this->entityManager->getRepository(urldecode($classname));
        $currentEntity = $repository->find($entityId);
        $setter = 'set' . ucfirst($property);
        $uniqProperties = ['isDefault', 'isMain'];

        if (method_exists($currentEntity, $setter)) {

            if ($status && in_array($property, $uniqProperties) && method_exists($currentEntity, 'getWebsite')) {
                $entities = $repository->findBy(['website' => $website]);
                foreach ($entities as $entity) {
                    $entity->$setter(false);
                    $this->entityManager->persist($entity);
                }
            }

            if($currentEntity instanceof Website) {
                $configuration = $currentEntity->getConfiguration();
                $configuration->setOnlineStatus($status);
                $this->entityManager->persist($configuration);
            }

            $currentEntity->$setter($status);
            $this->entityManager->persist($currentEntity);
            $this->entityManager->flush();
        }

        return new JsonResponse(['success' => true, 'reload' => in_array($property, $uniqProperties)]);
    }
}