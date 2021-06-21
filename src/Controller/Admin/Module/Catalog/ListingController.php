<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Listing;
use App\Form\Type\Module\Catalog\ListingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ListingController
 *
 * Catalog Listing management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/listings", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Listing $class
 * @property ListingType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingController extends AdminController
{
    protected $class = Listing::class;
    protected $formType = ListingType::class;

    /**
     * Index Product
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_cataloglisting_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Product
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_cataloglisting_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Product
     *
     * @Route("/edit/{cataloglisting}", methods={"GET", "POST"}, name="admin_cataloglisting_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Product
     *
     * @Route("/show/{cataloglisting}", methods={"GET"}, name="admin_cataloglisting_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Product
     *
     * @Route("/position/{cataloglisting}", methods={"GET", "POST"}, name="admin_cataloglisting_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Product
     *
     * @Route("/delete/{cataloglisting}", methods={"DELETE"}, name="admin_cataloglisting_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}