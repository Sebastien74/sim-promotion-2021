<?php

namespace App\Controller\Admin\Gdpr;

use App\Controller\Admin\AdminController;
use App\Entity\Gdpr\Category;
use App\Form\Type\Gdpr\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CategoryController
 *
 * Gdpr Category management
 *
 * @Route("/admin-%security_token%/{website}/gdpr/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
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
     * Index Gdpr Category
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_gdprcategory_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Gdpr Category
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_gdprcategory_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Gdpr Category
     *
     * @Route("/edit/{gdprcategory}", methods={"GET", "POST"}, name="admin_gdprcategory_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Gdpr Category
     *
     * @Route("/show/{gdprcategory}", methods={"GET"}, name="admin_gdprcategory_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Gdpr Category
     *
     * @Route("/position/{gdprcategory}", methods={"GET", "POST"}, name="admin_gdprcategory_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Gdpr Category
     *
     * @Route("/delete/{gdprcategory}", methods={"DELETE"}, name="admin_gdprcategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}