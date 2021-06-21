<?php

namespace App\Controller\Admin\Module\Portfolio;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Portfolio\Teaser;
use App\Form\Type\Module\Portfolio\TeaserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TeaserController
 *
 * Portfolio Teaser Action management
 *
 * @Route("/admin-%security_token%/{website}/portfolios/teasers", schemes={"%protocol%"})
 * @IsGranted("ROLE_PORTFOLIO")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_portfolioteaser_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_portfolioteaser_new")
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
     * @Route("/edit/{portfolioteaser}", methods={"GET", "POST"}, name="admin_portfolioteaser_edit")
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
     * @Route("/show/{portfolioteaser}", methods={"GET"}, name="admin_portfolioteaser_show")
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
     * @Route("/position/{portfolioteaser}", methods={"GET", "POST"}, name="admin_portfolioteaser_position")
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
     * @Route("/delete/{portfolioteaser}", methods={"DELETE"}, name="admin_portfolioteaser_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}