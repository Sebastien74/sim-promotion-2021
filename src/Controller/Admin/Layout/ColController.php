<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Col;
use App\Entity\Layout\Zone;
use App\Form\Manager\Layout\LayoutManager;
use App\Form\Type\Layout\Management\BackgroundColorColType;
use App\Form\Type\Layout\Management\ColConfigurationType;
use App\Form\Type\Layout\Management\ColSizeType;
use App\Form\Type\Layout\Management\ColType;
use App\Repository\Layout\ColRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ColController
 *
 * Layout Col management
 *
 * @Route("/admin-%security_token%/{website}/layouts/zones/cols", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Col $class
 * @property ColType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColController extends AdminController
{
    protected $class = Col::class;
    protected $formType = ColType::class;

    /**
     * New Col
     *
     * @Route("/new/{zone}", methods={"GET", "POST"}, name="admin_col_new")
     * @IsGranted("ROLE_EDIT")
     *
     * @param Request $request
     * @param Zone $zone
     * @return JsonResponse|Response
     */
    public function add(Request $request, Zone $zone)
    {
        $col = new Col();
        $form = $this->createForm(ColSizeType::class, $col);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $position = count($zone->getCols()) + 1;

            $col->setZone($zone);
            $col->setPosition($position);

            $this->entityManager->persist($col);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['html' => $this->renderView('admin/core/layout/new-col.html.twig', [
            'form' => $form->createView(),
            'zone' => $zone
        ])]);
    }

    /**
     * Edit Col
     *
     * @Route("/{zone}/{interfaceName}/{interfaceEntity}/edit/{col}", name="admin_col_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Edit background color Col
     *
     * @Route("/background/{col}", name="admin_col_background", options={"expose"=true})
     *
     * @param Request $request
     * @return Response
     */
    public function background(Request $request)
    {
        $this->disableFlash = true;
        $this->template = 'admin/core/layout/background.html.twig';
        $this->formType = BackgroundColorColType::class;
        return parent::edit($request);
    }

    /**
     * Set Col size
     *
     * @Route("/size/{col}/{size}", methods={"GET"}, name="admin_col_size", options={"expose"=true})
     * @IsGranted("ROLE_EDIT")
     *
     * @param Col $col
     * @param int $size
     * @return JsonResponse
     */
    public function size(Col $col, int $size)
    {
        $col->setSize($size);
        $this->entityManager->persist($col);
        $this->entityManager->flush();

        $this->subscriber->get(LayoutManager::class)->setGridZone($col->getZone()->getLayout());

        return new JsonResponse(['success' => true]);
    }

    /**
     * Standardize Block[] width in Col
     *
     * @Route("/standardize-elements/{col}", methods={"GET"}, name="admin_blocks_standardize", options={"expose"=true})
     *
     * @param Request $request
     * @param Col $col
     * @return JsonResponse
     */
    public function standardizeElements(Request $request, Col $col)
    {
        $col->setStandardizeElements($request->get('standardize'));
        $this->entityManager->persist($col);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * Col[] positions update
     *
     * @Route("/positions/pack/{data}", methods={"GET"}, name="admin_cols_positions", options={"expose"=true})
     *
     * @param ColRepository $colRepository
     * @param string $data
     * @return JsonResponse
     */
    public function positions(ColRepository $colRepository, string $data)
    {
        $colsData = explode('&', $data);

        foreach ($colsData as $colData) {
            $matches = explode('=', $colData);
            $col = $colRepository->find($matches[0]);
            $col->setPosition($matches[1]);
            $this->entityManager->persist($col);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Edit Col configuration
     *
     * @Route("/modal/configuration/{col}", name="admin_col_configuration")
     *
     * {@inheritdoc}
     */
    public function configuration(Request $request)
    {
        $this->disableFlash = true;
        $this->entity = $this->entityManager->getRepository(Col::class)->find($request->get('col'));
        $this->formType = ColConfigurationType::class;
        $this->template = 'admin/core/layout/col-configuration.html.twig';
        $this->arguments['col'] = $this->entity;

        return parent::edit($request);
    }

    /**
     * Delete Col
     *
     * @Route("/{zone}/delete/{col}", methods={"DELETE"}, name="admin_col_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}