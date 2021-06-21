<?php

namespace App\Controller\Admin\Module\Agenda;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Agenda\Agenda;
use App\Form\Type\Module\Agenda\AgendaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AgendaController
 *
 * Agenda Action management
 *
 * @Route("/admin-%security_token%/{website}/agendas", schemes={"%protocol%"})
 * @IsGranted("ROLE_AGENDA")
 *
 * @property Agenda $class
 * @property AgendaType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AgendaController extends AdminController
{
    protected $class = Agenda::class;
    protected $formType = AgendaType::class;

    /**
     * Index Agenda
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_agenda_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Agenda
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_agenda_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Agenda
     *
     * @Route("/edit/{agenda}", methods={"GET", "POST"}, name="admin_agenda_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Position Agenda
     *
     * @Route("/position/{agenda}", methods={"GET", "POST"}, name="admin_agenda_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Agenda
     *
     * @Route("/delete/{agenda}", methods={"DELETE"}, name="admin_agenda_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}