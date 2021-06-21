<?php

namespace App\Controller\Admin\Module\Making;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Making\Category;
use App\Form\Type\Module\Making\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Making Category Action management
 *
 * @Route("/admin-%security_token%/{website}/makings/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAKING")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_makingcategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_makingcategory_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Layout Category
     *
     * @Route("/layout/{makingcategory}", methods={"GET", "POST"}, name="admin_makingcategory_layout")
     *
     * {@inheritdoc}
     */
    public function layout(Request $request)
    {
        return parent::layout($request);
    }

    /**
     * Show Category
     *
     * @Route("/show/{makingcategory}", methods={"GET"}, name="admin_makingcategory_show")
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
     * @Route("/position/{makingcategory}", methods={"GET", "POST"}, name="admin_makingcategory_position")
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
     * @Route("/delete/{makingcategory}", methods={"DELETE"}, name="admin_makingcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

}