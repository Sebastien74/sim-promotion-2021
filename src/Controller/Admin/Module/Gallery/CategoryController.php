<?php

namespace App\Controller\Admin\Module\Gallery;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Gallery\Category;
use App\Form\Type\Module\Gallery\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GalleryController
 *
 * Gallery Category Action management
 *
 * @Route("/admin-%security_token%/{website}/galleries/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_GALLERY")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_gallerycategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_gallerycategory_new")
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
     * @Route("/edit/{gallerycategory}", methods={"GET", "POST"}, name="admin_gallerycategory_edit")
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
     * @Route("/show/{gallerycategory}", methods={"GET"}, name="admin_gallerycategory_show")
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
     * @Route("/position/{gallerycategory}", methods={"GET", "POST"}, name="admin_gallerycategory_position")
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
     * @Route("/delete/{gallerycategory}", methods={"DELETE"}, name="admin_gallerycategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}