<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\ContactForm;
use App\Service\Delete\ContactDeleteService;
use App\Service\Export\ExportContactService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FormContactController
 *
 * Form Contact management
 *
 * @Route("/admin-%security_token%/{website}/forms/contacts", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM")
 *
 * @property ContactForm $class
 * @property ContactDeleteService $deleteService
 * @property ExportContactService $exportService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactFormController extends AdminController
{
    protected $class = ContactForm::class;
    protected $deleteService = ContactDeleteService::class;
    protected $exportService = ExportContactService::class;

    /**
     * Index ContactForm
     *
     * @Route("/{form}/index", methods={"GET", "POST"}, name="admin_formcontact_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Show ContactForm
     *
     * @Route("/{form}/show/{formcontact}", methods={"GET"}, name="admin_formcontact_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Export ContactForm[]
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_formcontact_export")
     *
     * {@inheritdoc}
     */
    public function export(Request $request)
    {
        return parent::export($request);
    }

    /**
     * Delete ContactForm
     *
     * @Route("/delete/{formcontact}", methods={"DELETE"}, name="admin_formcontact_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}