<?php

namespace App\Controller\Admin\Module\Timeline;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Timeline\Step;
use App\Form\Type\Module\Timeline\StepType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * StepController
 *
 * Step Timeline management
 *
 * @Route("/admin-%security_token%/{website}/timelines/steps", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM")
 *
 * @property Step $class
 * @property StepType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepController extends AdminController
{
    protected $class = Step::class;
    protected $formType = StepType::class;

    /**
     * Index Step
     *
     * @Route("/{timeline}/index", methods={"GET", "POST"}, name="admin_timelinestep_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Step
     *
     * @Route("/{timeline}/new", methods={"GET", "POST"}, name="admin_timelinestep_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Step
     *
     * @Route("/{timeline}/edit/{timelinestep}", methods={"GET", "POST"}, name="admin_timelinestep_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Step
     *
     * @Route("/{timeline}/show/{timelinestep}", methods={"GET"}, name="admin_timelinestep_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Step
     *
     * @Route("/position/{timelinestep}", methods={"GET", "POST"}, name="admin_timelinestep_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Step
     *
     * @Route("/delete/{timelinestep}", methods={"DELETE"}, name="admin_timelinestep_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}