<?php

namespace App\Controller\Admin\Module\Contact;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Contact\Contact;
use App\Form\Type\Module\Contact\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ContactController
 *
 * Contact Action management
 *
 * @Route("/admin-%security_token%/{website}/contacts", schemes={"%protocol%"})
 * @IsGranted("ROLE_CONTACT")
 *
 * @property Contact $class
 * @property ContactType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactController extends AdminController
{
    protected $class = Contact::class;
    protected $formType = ContactType::class;

    /**
     * Index Contact
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_contact_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Contact
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_contact_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Contact
     *
     * @Route("/edit/{contact}", methods={"GET", "POST"}, name="admin_contact_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Contact
     *
     * @Route("/show/{contact}", methods={"GET"}, name="admin_contact_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Contact
     *
     * @Route("/position/{contact}", methods={"GET", "POST"}, name="admin_contact_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Contact
     *
     * @Route("/delete/{contact}", methods={"DELETE"}, name="admin_contact_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}