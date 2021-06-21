<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Category;
use App\Form\Type\Module\Catalog\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Catalog Category management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
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
     * Index Category
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_catalogcategory_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Category
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_catalogcategory_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Category
     *
     * @Route("/edit/{catalogcategory}", methods={"GET", "POST"}, name="admin_catalogcategory_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Category
     *
     * @Route("/show/{catalogcategory}", methods={"GET"}, name="admin_catalogcategory_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Category
     *
     * @Route("/position/{catalogcategory}", methods={"GET", "POST"}, name="admin_catalogcategory_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Category
     *
     * @Route("/delete/{catalogcategory}", methods={"DELETE"}, name="admin_catalogcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}