<?php

namespace App\Controller\Admin\Module\Faq;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Faq\Faq;
use App\Form\Type\Module\Faq\FaqType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FaqController
 *
 * Faq Action management
 *
 * @Route("/admin-%security_token%/{website}/faqs", schemes={"%protocol%"})
 * @IsGranted("ROLE_FAQ")
 *
 * @property Faq $class
 * @property FaqType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FaqController extends AdminController
{
    protected $class = Faq::class;
    protected $formType = FaqType::class;

    /**
     * Index Faq
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_faq_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Faq
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_faq_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Faq
     *
     * @Route("/edit/{faq}", methods={"GET", "POST"}, name="admin_faq_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Faq
     *
     * @Route("/show/{faq}", methods={"GET"}, name="admin_faq_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Faq
     *
     * @Route("/position/{faq}", methods={"GET", "POST"}, name="admin_faq_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Faq
     *
     * @Route("/delete/{faq}", methods={"DELETE"}, name="admin_faq_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}