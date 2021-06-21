<?php

namespace App\Controller\Admin\Module\Event;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Event\Teaser;
use App\Form\Type\Module\Event\TeaserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TeaserController
 *
 * Event Teaser Action management
 *
 * @Route("/admin-%security_token%/{website}/events/teasers", schemes={"%protocol%"})
 * @IsGranted("ROLE_EVENT")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_eventteaser_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_eventteaser_new")
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
     * @Route("/edit/{eventteaser}", methods={"GET", "POST"}, name="admin_eventteaser_edit")
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
     * @Route("/show/{eventteaser}", methods={"GET"}, name="admin_eventteaser_show")
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
     * @Route("/position/{eventteaser}", methods={"GET", "POST"}, name="admin_eventteaser_position")
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
     * @Route("/delete/{eventteaser}", methods={"DELETE"}, name="admin_eventteaser_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}