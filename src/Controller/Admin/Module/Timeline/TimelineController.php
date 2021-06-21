<?php

namespace App\Controller\Admin\Module\Timeline;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Timeline\Timeline;
use App\Form\Type\Module\Timeline\TimelineType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TimelineController
 *
 * Timeline Action management
 *
 * @Route("/admin-%security_token%/{website}/timelines", schemes={"%protocol%"})
 * @IsGranted("ROLE_TIMELINE")
 *
 * @property Timeline $class
 * @property TimelineType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TimelineController extends AdminController
{
    protected $class = Timeline::class;
    protected $formType = TimelineType::class;

    /**
     * Index Timeline
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_timeline_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Timeline
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_timeline_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Timeline
     *
     * @Route("/edit/{timeline}", methods={"GET", "POST"}, name="admin_timeline_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Position Timeline
     *
     * @Route("/position/{timeline}", methods={"GET", "POST"}, name="admin_timeline_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Timeline
     *
     * @Route("/delete/{timeline}", methods={"DELETE"}, name="admin_timeline_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}