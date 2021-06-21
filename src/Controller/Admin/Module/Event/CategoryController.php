<?php

namespace App\Controller\Admin\Module\Event;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Event\Category;
use App\Form\Type\Module\Event\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Event Category Action management
 *
 * @Route("/admin-%security_token%/{website}/events/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_EVENT")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_eventcategory_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_eventcategory_new")
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
     * @Route("/layout/{eventcategory}", methods={"GET", "POST"}, name="admin_eventcategory_layout")
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
     * @Route("/show/{eventcategory}", methods={"GET"}, name="admin_eventcategory_show")
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
     * @Route("/position/{eventcategory}", methods={"GET", "POST"}, name="admin_eventcategory_position")
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
     * @Route("/delete/{eventcategory}", methods={"DELETE"}, name="admin_eventcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}