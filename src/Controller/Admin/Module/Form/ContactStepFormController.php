<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\ContactStepForm;
use App\Form\Manager\Delete\ContactDeleteManager;
use App\Service\Delete\ContactDeleteService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ContactStepFormController
 *
 * Form Contact management
 *
 * @Route("/admin-%security_token%/{website}/steps/forms/contacts", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM")
 *
 * @property ContactStepForm $class
 * @property ContactDeleteService $deleteService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactStepFormController extends AdminController
{
    protected $class = ContactStepForm::class;
    protected $deleteService = ContactDeleteService::class;

    /**
     * Index ContactStepForm
     *
     * @Route("/index/{stepform}", methods={"GET", "POST"}, name="admin_contactstepform_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Show ContactStepForm
     *
     * @Route("/{stepform}/show/{contactstepform}", methods={"GET"}, name="admin_contactstepform_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete ContactStepForm
     *
     * @Route("/delete/{contactstepform}", methods={"DELETE"}, name="admin_contactstepform_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}