<?php

namespace App\Controller\Admin\Module\Map;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Map\Category;
use App\Form\Type\Module\Map\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Map Category Action management
 *
 * @Route("/admin-%security_token%/{website}/maps/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAP")
 *
 * @property Category $class
 * @property CategoryType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryController extends AdminController
{
    protected $class = Category::class;
    protected $formType = CategoryType::class;

    /**
     * Index Map
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_mapcategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_mapcategory_new")
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
     * @Route("/edit/{mapcategory}", methods={"GET", "POST"}, name="admin_mapcategory_edit")
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
     * @Route("/show/{mapcategory}", methods={"GET"}, name="admin_mapcategory_show")
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
     * @Route("/position/{mapcategory}", methods={"GET", "POST"}, name="admin_mapcategory_position")
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
     * @Route("/delete/{mapcategory}", methods={"DELETE"}, name="admin_mapcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}