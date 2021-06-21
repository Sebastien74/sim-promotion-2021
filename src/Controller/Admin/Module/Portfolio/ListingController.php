<?php

namespace App\Controller\Admin\Module\Portfolio;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Portfolio\Listing;
use App\Form\Type\Module\Portfolio\ListingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ListingController
 *
 * Portfolio Listing Action management
 *
 * @Route("/admin-%security_token%/{website}/portfolios/listings", schemes={"%protocol%"})
 * @IsGranted("ROLE_PORTFOLIO")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_portfoliolisting_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_portfoliolisting_new")
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
     * @Route("/edit/{portfoliolisting}", methods={"GET", "POST"}, name="admin_portfoliolisting_edit")
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
     * @Route("/show/{portfoliolisting}", methods={"GET"}, name="admin_portfoliolisting_show")
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
     * @Route("/position/{portfoliolisting}", methods={"GET", "POST"}, name="admin_portfoliolisting_position")
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
     * @Route("/delete/{portfoliolisting}", methods={"DELETE"}, name="admin_portfoliolisting_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}