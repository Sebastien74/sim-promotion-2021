<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Module;
use App\Form\Type\Core\ModuleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ModuleController
 *
 * Module management
 *
 * @Route("/admin-%security_token%/{website}/configuration/modules", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Module $class
 * @property ModuleType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModuleController extends AdminController
{
    protected $class = Module::class;
    protected $formType = ModuleType::class;

    /**
     * Index Module
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_module_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Module
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_module_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Module
     *
     * @Route("/edit/{module}", methods={"GET", "POST"}, name="admin_module_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Module
     *
     * @Route("/show/{module}", methods={"GET"}, name="admin_module_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Module
     *
     * @Route("/position/{module}", methods={"GET", "POST"}, name="admin_module_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Module
     *
     * @Route("/delete/{module}", methods={"DELETE"}, name="admin_module_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}