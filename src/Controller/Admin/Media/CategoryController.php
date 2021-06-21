<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\Category;
use App\Form\Type\Media\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Media Category management
 *
 * @Route("/admin-%security_token%/{website}/medias/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_mediacategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_mediacategory_new")
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
     * @Route("/edit/{mediacategory}", methods={"GET", "POST"}, name="admin_mediacategory_edit")
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
     * @Route("/show/{mediacategory}", methods={"GET"}, name="admin_mediacategory_show")
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
     * @Route("/position/{mediacategory}", methods={"GET", "POST"}, name="admin_mediacategory_position")
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
     * @Route("/delete/{mediacategory}", methods={"DELETE"}, name="admin_mediacategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}