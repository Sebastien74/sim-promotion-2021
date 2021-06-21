<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Grid;
use App\Form\Type\Layout\Management\GridType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GridController
 *
 * Layout Grid management
 *
 * @Route("/admin-%security_token%/{website}/layouts/grids", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Grid $class
 * @property GridType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridController extends AdminController
{
    protected $class = Grid::class;
    protected $formType = GridType::class;

    /**
     * Index Grid
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_grid_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Grid
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_grid_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Grid
     *
     * @Route("/edit/{grid}", methods={"GET", "POST"}, name="admin_grid_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Grid
     *
     * @Route("/show/{grid}", methods={"GET"}, name="admin_grid_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Grid
     *
     * @Route("/position/{grid}", methods={"GET", "POST"}, name="admin_grid_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Grid
     *
     * @Route("/delete/{grid}", methods={"DELETE"}, name="admin_grid_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}