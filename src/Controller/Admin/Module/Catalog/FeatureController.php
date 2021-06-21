<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Feature;
use App\Form\Type\Module\Catalog\FeatureType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FeatureController
 *
 * Catalog Feature management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/features", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Feature $class
 * @property FeatureType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureController extends AdminController
{
    protected $class = Feature::class;
    protected $formType = FeatureType::class;

    /**
     * Index Feature
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_catalogfeature_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Feature
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_catalogfeature_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Feature
     *
     * @Route("/edit/{catalogfeature}", methods={"GET", "POST"}, name="admin_catalogfeature_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Feature
     *
     * @Route("/show/{catalogfeature}", methods={"GET"}, name="admin_catalogfeature_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Feature
     *
     * @Route("/position/{catalogfeature}", methods={"GET", "POST"}, name="admin_catalogfeature_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Feature
     *
     * @Route("/delete/{catalogfeature}", methods={"DELETE"}, name="admin_catalogfeature_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}