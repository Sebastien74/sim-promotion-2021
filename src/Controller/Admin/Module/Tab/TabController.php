<?php

namespace App\Controller\Admin\Module\Tab;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Tab\Tab;
use App\Form\Type\Module\Tab\TabType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TabController
 *
 * Tab Action management
 *
 * @Route("/admin-%security_token%/{website}/tabs", schemes={"%protocol%"})
 * @IsGranted("ROLE_TAB")
 *
 * @property Tab $class
 * @property TabType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TabController extends AdminController
{
    protected $class = Tab::class;
    protected $formType = TabType::class;

    /**
     * Index Tab
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_tab_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Tab
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_tab_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Tab
     *
     * @Route("/edit/{tab}", methods={"GET", "POST"}, name="admin_tab_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Tab
     *
     * @Route("/show/{tab}", methods={"GET"}, name="admin_tab_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Tab
     *
     * @Route("/position/{tab}", methods={"GET", "POST"}, name="admin_tab_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Tab
     *
     * @Route("/delete/{tab}", methods={"DELETE"}, name="admin_tab_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}