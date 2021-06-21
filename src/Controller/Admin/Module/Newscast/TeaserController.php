<?php

namespace App\Controller\Admin\Module\Newscast;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newscast\Teaser;
use App\Form\Type\Module\Newscast\TeaserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TeaserController
 *
 * Newscast Teaser Action management
 *
 * @Route("/admin-%security_token%/{website}/newscasts/teasers", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSCAST")
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
     * @Route("/index", methods={"GET", "POST"}, name="admin_newscastteaser_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_newscastteaser_new")
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
     * @Route("/edit/{newscastteaser}", methods={"GET", "POST"}, name="admin_newscastteaser_edit")
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
     * @Route("/show/{newscastteaser}", methods={"GET"}, name="admin_newscastteaser_show")
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
     * @Route("/position/{newscastteaser}", methods={"GET", "POST"}, name="admin_newscastteaser_position")
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
     * @Route("/delete/{newscastteaser}", methods={"DELETE"}, name="admin_newscastteaser_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}