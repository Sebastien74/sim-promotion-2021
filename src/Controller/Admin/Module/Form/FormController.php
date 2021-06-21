<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\Form;
use App\Form\Manager\Module\FormManager;
use App\Form\Type\Module\Form\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * FormController
 *
 * Form Action management
 *
 * @Route("/admin-%security_token%/{website}/forms", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM")
 *
 * @property Form $class
 * @property FormType $formType
 * @property FormManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormController extends AdminController
{
    protected $class = Form::class;
    protected $formType = FormType::class;
    protected $formManager = FormManager::class;

    /**
     * Index Form
     *
     * @Route("/index/{stepform}", defaults={"stepform": null}, methods={"GET", "POST"}, name="admin_form_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        if (!empty($request->get('stepform'))) {
            $this->pageTitle = $this->translator->trans('Étapes', [], 'admin');
        }

        return parent::index($request);
    }

    /**
     * New Form
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_form_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Layout Form
     *
     * @Route("/layout/{form}/{stepform}", defaults={"stepform": null}, methods={"GET", "POST"}, name="admin_form_layout")
     *
     * {@inheritdoc}
     */
    public function layout(Request $request)
    {
        return parent::layout($request);
    }

    /**
     * Show Form
     *
     * @Route("/show/{form}", methods={"GET"}, name="admin_form_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Form
     *
     * @Route("/position/{form}", methods={"GET", "POST"}, name="admin_form_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Form
     *
     * @Route("/delete/{form}", methods={"DELETE"}, name="admin_form_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}