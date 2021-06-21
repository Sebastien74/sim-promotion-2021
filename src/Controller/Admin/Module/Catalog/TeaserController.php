<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Teaser;
use App\Form\Type\Module\Catalog\TeaserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TeaserController
 *
 * Catalog Teaser Product[] management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/teasers", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Teaser $class
 * @property TeaserType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TeaserController extends AdminController
{
    protected $class = Teaser::class;
    protected $formType = TeaserType::class;

    /**
     * Index Teaser
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_productteaser_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Teaser
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_productteaser_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Teaser
     *
     * @Route("/edit/{productteaser}", methods={"GET", "POST"}, name="admin_productteaser_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Teaser
     *
     * @Route("/show/{productteaser}", methods={"GET"}, name="admin_productteaser_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Teaser
     *
     * @Route("/position/{productteaser}", methods={"GET", "POST"}, name="admin_productteaser_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Teaser
     *
     * @Route("/delete/{productteaser}", methods={"DELETE"}, name="admin_productteaser_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}