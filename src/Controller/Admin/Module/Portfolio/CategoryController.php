<?php

namespace App\Controller\Admin\Module\Portfolio;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Portfolio\Category;
use App\Form\Type\Module\Portfolio\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Portfolio Category Action management
 *
 * @Route("/admin-%security_token%/{website}/portfolios/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_PORTFOLIO")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_portfoliocategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_portfoliocategory_new")
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
     * @Route("/layout/{portfoliocategory}", methods={"GET", "POST"}, name="admin_portfoliocategory_layout")
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
     * @Route("/show/{portfoliocategory}", methods={"GET"}, name="admin_portfoliocategory_show")
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
     * @Route("/position/{portfoliocategory}", methods={"GET", "POST"}, name="admin_portfoliocategory_position")
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
     * @Route("/delete/{portfoliocategory}", methods={"DELETE"}, name="admin_portfoliocategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}