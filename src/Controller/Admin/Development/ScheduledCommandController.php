<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Entity\Core\ScheduledCommand;
use App\Form\Type\Development\ScheduledCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ScheduledCommandController
 *
 * Scheduled command management
 *
 * @Route("/admin-%security_token%/{website}/development/scheduled-command", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property ScheduledCommand $class
 * @property ScheduledCommandType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ScheduledCommandController extends AdminController
{
    protected $class = ScheduledCommand::class;
    protected $formType = ScheduledCommandType::class;

    /**
     * Index ScheduledCommand
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_command_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New ScheduledCommand
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_command_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit ScheduledCommand
     *
     * @Route("/edit/{command}", methods={"GET", "POST"}, name="admin_command_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show ScheduledCommand
     *
     * @Route("/show/{command}", methods={"GET"}, name="admin_command_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete ScheduledCommand
     *
     * @Route("/delete/{command}", methods={"DELETE"}, name="admin_command_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}