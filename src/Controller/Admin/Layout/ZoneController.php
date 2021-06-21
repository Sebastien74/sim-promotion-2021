<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Zone;
use App\Form\Manager\Layout\ZoneConfigurationManager;
use App\Form\Manager\Layout\ZoneDuplicateManager;
use App\Form\Manager\Layout\ZoneManager;
use App\Form\Type\Layout\Management\BackgroundColorZoneType;
use App\Form\Type\Layout\Management\ZoneConfigurationType;
use App\Form\Type\Layout\Management\ZoneDuplicateType;
use App\Form\Type\Layout\Management\ZoneGridType;
use App\Form\Type\Layout\Management\ZoneType;
use App\Repository\Layout\ZoneRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ZoneController
 *
 * Layout Zone management
 *
 * @Route("/admin-%security_token%/{website}/layouts/zones", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Zone $class
 * @property ZoneType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneController extends AdminController
{
    protected $class = Zone::class;
    protected $formType = ZoneType::class;

    /**
     * New Zone
     *
     * @Route("/new/{layout}", methods={"GET", "POST"}, name="admin_zone_new")
     * @IsGranted("ROLE_EDIT")
     *
     * @param Request $request
     * @param Layout $layout
     * @return JsonResponse|Response
     */
    public function add(Request $request, Layout $layout)
    {
        $form = $this->createForm(ZoneGridType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->subscriber->get(ZoneManager::class)->add($layout, $form);
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['html' => $this->renderView('admin/core/layout/new-zone.html.twig', [
            'form' => $form->createView(),
            'layout' => $layout
        ])]);
    }

    /**
     * Edit Zone
     *
     * @Route("/{layout}/{interfaceName}/{interfaceEntity}/edit/{zone}", name="admin_zone_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Edit background color Zone
     *
     * @Route("/background/{zone}", name="admin_zone_background")
     *
     * @param Request $request
     * @return Response
     */
    public function background(Request $request)
    {
        $this->disableFlash = true;
        $this->template = 'admin/core/layout/background.html.twig';
        $this->formType = BackgroundColorZoneType::class;
        return parent::edit($request);
    }

    /**
     * Duplicate Zone
     *
     * @Route("/duplicate/{zone}", methods={"GET", "POST"}, name="admin_zone_duplicate")
     *
     * {@inheritdoc}
     */
    public function duplicate(Request $request)
    {
        $this->formType = ZoneDuplicateType::class;
        $this->formDuplicateManager = ZoneDuplicateManager::class;
        return parent::duplicate($request);
    }

    /**
     * Zone[] positions update
     *
     * @Route("/positions/pack/{data}", methods={"GET"}, name="admin_zones_positions", options={"expose"=true})
     *
     * @param ZoneRepository $zoneRepository
     * @param string $data
     * @return JsonResponse
     */
    public function positions(ZoneRepository $zoneRepository, string $data)
    {
        $zonesData = explode('&', $data);

        foreach ($zonesData as $zoneData) {
            $matches = explode('=', $zoneData);
            $zone = $zoneRepository->find($matches[0]);
            $zone->setPosition($matches[1]);
            $this->entityManager->persist($zone);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Size Zone
     *
     * @Route("/size/{zone}", methods={"GET"}, name="admin_zone_size", options={"expose"=true})
     *
     * @param Request $request
     * @param Zone $zone
     * @return JsonResponse
     */
    public function size(Request $request, Zone $zone)
    {
        $zone->setFullSize($request->get('size'));
        $this->entityManager->persist($zone);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * Standardize Col[] width in Zone
     *
     * @Route("/standardize-elements/{zone}", methods={"GET"}, name="admin_cols_standardize", options={"expose"=true})
     *
     * @param Request $request
     * @param Zone $zone
     * @return JsonResponse
     */
    public function standardizeElements(Request $request, Zone $zone)
    {
        $zone->setStandardizeElements($request->get('standardize'));
        $this->entityManager->persist($zone);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * Edit Zone configuration
     *
     * @Route("/modal/configuration/{zone}", name="admin_zone_configuration")
     *
     * @param Request $request
     * @return Response
     */
    public function configuration(Request $request)
    {
        $this->disableFlash = true;
        $this->entity = $this->entityManager->getRepository(Zone::class)->find($request->get('zone'));
        $this->formType = ZoneConfigurationType::class;
        $this->formManager = ZoneConfigurationManager::class;
        $this->template = 'admin/core/layout/zone-configuration.html.twig';
        $this->arguments['zone'] = $this->entity;

        return parent::edit($request);
    }

    /**
     * Delete Zone
     *
     * @Route("/{layout}/delete/{zone}", methods={"DELETE"}, name="admin_zone_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}