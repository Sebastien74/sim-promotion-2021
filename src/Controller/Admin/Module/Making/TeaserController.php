<?php

namespace App\Controller\Admin\Module\Making;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Making\Teaser;
use App\Form\Type\Module\Making\TeaserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TeaserController
 *
 * Teaser Action management
 *
 * @Route("/admin-%security_token%/{website}/makings/teasers", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAKING")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_makingteaser_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_makingteaser_new")
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
     * @Route("/edit/{makingteaser}", methods={"GET", "POST"}, name="admin_makingteaser_edit")
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
     * @Route("/show/{makingteaser}", methods={"GET"}, name="admin_makingteaser_show")
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
     * @Route("/position/{makingteaser}", methods={"GET", "POST"}, name="admin_makingteaser_position")
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
     * @Route("/delete/{makingteaser}", methods={"DELETE"}, name="admin_makingteaser_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}