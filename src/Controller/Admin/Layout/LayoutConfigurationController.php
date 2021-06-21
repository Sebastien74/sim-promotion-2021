<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\LayoutConfiguration;
use App\Form\Manager\Layout\LayoutConfigurationManager;
use App\Form\Type\Layout\Management\LayoutConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LayoutController
 *
 * Layout management
 *
 * @Route("/admin-%security_token%/{website}/layouts/configurations", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property LayoutConfiguration $class
 * @property LayoutConfigurationType $formType
 * @property LayoutConfigurationManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutConfigurationController extends AdminController
{
    protected $class = LayoutConfiguration::class;
    protected $formType = LayoutConfigurationType::class;
    protected $formManager = LayoutConfigurationManager::class;

    /**
     * Index LayoutConfiguration
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_layoutconfiguration_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New LayoutConfiguration
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_layoutconfiguration_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit LayoutConfiguration
     *
     * @Route("/new/{layoutconfiguration}", methods={"GET", "POST"}, name="admin_layoutconfiguration_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Delete LayoutConfiguration
     *
     * @Route("/delete/{layoutconfiguration}", methods={"DELETE"}, name="admin_layoutconfiguration_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}