<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\FeatureValue;
use App\Form\Manager\Module\CatalogFeatureValueManager;
use App\Form\Type\Module\Catalog\FeatureValueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FeatureValueController
 *
 * Catalog FeatureValue management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/features/values", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property FeatureValue $class
 * @property FeatureValueType $formType
 * @property CatalogFeatureValueManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueController extends AdminController
{
    protected $class = FeatureValue::class;
    protected $formType = FeatureValueType::class;
    protected $formManager = CatalogFeatureValueManager::class;

    /**
     * Index FeatureValue
     *
     * @Route("/{catalogfeature}/index", methods={"GET", "POST"}, name="admin_catalogfeaturevalue_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New FeatureValue
     *
     * @Route("/{catalogfeature}/new", methods={"GET", "POST"}, name="admin_catalogfeaturevalue_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit FeatureValue
     *
     * @Route("/{catalogfeature}/edit/{catalogfeaturevalue}", methods={"GET", "POST"}, name="admin_catalogfeaturevalue_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show FeatureValue
     *
     * @Route("/{catalogfeature}/show/{catalogfeaturevalue}", methods={"GET"}, name="admin_catalogfeaturevalue_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position FeatureValue
     *
     * @Route("/position/{catalogfeaturevalue}", methods={"GET", "POST"}, name="admin_catalogfeaturevalue_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete FeatureValue
     *
     * @Route("/delete/{catalogfeaturevalue}", methods={"DELETE"}, name="admin_catalogfeaturevalue_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}