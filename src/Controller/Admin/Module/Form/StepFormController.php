<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\StepForm;
use App\Form\Manager\Module\StepFormManager;
use App\Form\Type\Module\Form\StepFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * StepFormController
 *
 * Steps Form Action management
 *
 * @Route("/admin-%security_token%/{website}/steps/forms", schemes={"%protocol%"})
 * @IsGranted("ROLE_STEP_FORM")
 *
 * @property StepForm $class
 * @property StepFormType $formType
 * @property StepFormManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepFormController extends AdminController
{
    protected $class = StepForm::class;
    protected $formType = StepFormType::class;
    protected $formManager = StepFormManager::class;

    /**
     * Index StepForm
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_stepform_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New StepForm
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_stepform_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit StepForm
     *
     * @Route("/edit/{stepform}", methods={"GET", "POST"}, name="admin_stepform_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show StepForm
     *
     * @Route("/show/{stepform}", methods={"GET"}, name="admin_stepform_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete StepForm
     *
     * @Route("/delete/{stepform}", methods={"DELETE"}, name="admin_stepform_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}