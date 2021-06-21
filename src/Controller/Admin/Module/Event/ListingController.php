<?php

namespace App\Controller\Admin\Module\Event;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Event\Listing;
use App\Form\Type\Module\Event\ListingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ListingController
 *
 * Event Listing Action management
 *
 * @Route("/admin-%security_token%/{website}/events/listings", schemes={"%protocol%"})
 * @IsGranted("ROLE_EVENT")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_eventlisting_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_eventlisting_new")
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
     * @Route("/edit/{eventlisting}", methods={"GET", "POST"}, name="admin_eventlisting_edit")
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
     * @Route("/show/{eventlisting}", methods={"GET"}, name="admin_eventlisting_show")
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
     * @Route("/position/{eventlisting}", methods={"GET", "POST"}, name="admin_eventlisting_position")
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
     * @Route("/delete/{eventlisting}", methods={"DELETE"}, name="admin_eventlisting_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}