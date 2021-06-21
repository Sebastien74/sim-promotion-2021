<?php

namespace App\Controller\Admin\Module\Newscast;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newscast\Category;
use App\Form\Type\Module\Newscast\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Newscast Category Action management
 *
 * @Route("/admin-%security_token%/{website}/newscasts/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSCAST")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_newscastcategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_newscastcategory_new")
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
     * @Route("/layout/{newscastcategory}", methods={"GET", "POST"}, name="admin_newscastcategory_layout")
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
     * @Route("/show/{newscastcategory}", methods={"GET"}, name="admin_newscastcategory_show")
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
     * @Route("/position/{newscastcategory}", methods={"GET", "POST"}, name="admin_newscastcategory_position")
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
     * @Route("/delete/{newscastcategory}", methods={"DELETE"}, name="admin_newscastcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}