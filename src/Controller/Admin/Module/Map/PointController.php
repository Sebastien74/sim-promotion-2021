<?php

namespace App\Controller\Admin\Module\Map;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Map\Point;
use App\Form\Type\Module\Map\PointType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PointController
 *
 * Map Point Action management
 *
 * @Route("/admin-%security_token%/{website}/maps/points", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAP")
 *
 * @property Point $class
 * @property PointType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PointController extends AdminController
{
    protected $class = Point::class;
    protected $formType = PointType::class;

    /**
     * Index Point
     *
     * @Route("/{map}/index", methods={"GET", "POST"}, name="admin_mappoint_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Point
     *
     * @Route("/{map}/new", methods={"GET", "POST"}, name="admin_mappoint_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Point
     *
     * @Route("/{map}/edit/{mappoint}", methods={"GET", "POST"}, name="admin_mappoint_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Point
     *
     * @Route("/{map}/show/{mappoint}", methods={"GET"}, name="admin_mappoint_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete Point
     *
     * @Route("/delete/{mappoint}", methods={"DELETE"}, name="admin_mappoint_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}