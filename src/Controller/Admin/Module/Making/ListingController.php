<?php

namespace App\Controller\Admin\Module\Making;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Making\Listing;
use App\Form\Type\Module\Making\ListingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ListingController
 *
 * Making Category Action management
 *
 * @Route("/admin-%security_token%/{website}/makings/listings", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAKING")
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
     * Index Listing
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_makinglisting_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Listing
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_makinglisting_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Listing
     *
     * @Route("/edit/{makinglisting}", methods={"GET", "POST"}, name="admin_makinglisting_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Listing
     *
     * @Route("/show/{makinglisting}", methods={"GET"}, name="admin_makinglisting_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Listing
     *
     * @Route("/position/{makinglisting}", methods={"GET", "POST"}, name="admin_makinglisting_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Listing
     *
     * @Route("/delete/{makinglisting}", methods={"DELETE"}, name="admin_makinglisting_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}