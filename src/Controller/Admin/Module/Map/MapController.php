<?php

namespace App\Controller\Admin\Module\Map;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Map\Map;
use App\Form\Type\Module\Map\MapType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MapController
 *
 * Map Action management
 *
 * @Route("/admin-%security_token%/{website}/maps", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAP")
 *
 * @property Map $class
 * @property MapType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MapController extends AdminController
{
    protected $class = Map::class;
    protected $formType = MapType::class;

    /**
     * Index Map
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_map_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Map
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_map_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Map
     *
     * @Route("/edit/{map}", methods={"GET", "POST"}, name="admin_map_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Map
     *
     * @Route("/show/{map}", methods={"GET"}, name="admin_map_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Map
     *
     * @Route("/position/{map}", methods={"GET", "POST"}, name="admin_map_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Map
     *
     * @Route("/delete/{map}", methods={"DELETE"}, name="admin_map_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}