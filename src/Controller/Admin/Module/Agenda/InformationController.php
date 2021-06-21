<?php

namespace App\Controller\Admin\Module\Agenda;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Agenda\Information;
use App\Form\Type\Module\Agenda\InformationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * InformationController
 *
 * Agenda Information Action management
 *
 * @Route("/admin-%security_token%/{website}/agendas/informations", schemes={"%protocol%"})
 * @IsGranted("ROLE_AGENDA")
 *
 * @property Information $class
 * @property InformationType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationController extends AdminController
{
    protected $class = Information::class;
    protected $formType = InformationType::class;

    /**
     * Index Information
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_agendainformation_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Information
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_agendainformation_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Information
     *
     * @Route("/edit/{agendainformation}", methods={"GET", "POST"}, name="admin_agendainformation_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Position Information
     *
     * @Route("/position/{agendainformation}", methods={"GET", "POST"}, name="admin_agendainformation_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Information
     *
     * @Route("/delete/{agendainformation}", methods={"DELETE"}, name="admin_agendainformation_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}