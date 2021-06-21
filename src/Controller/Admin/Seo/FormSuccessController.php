<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\FormSuccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FormSuccessController
 *
 * FormSuccess management
 *
 * @Route("/admin-%security_token%/{website}/seo/form/success", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property FormSuccess $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormSuccessController extends AdminController
{
    protected $class = FormSuccess::class;

    /**
     * Index FormSuccess
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_formsuccess_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        $this->template = 'admin/page/seo/form-success.html.twig';

        return parent::index($request);
    }

    /**
     * Delete FormSuccess
     *
     * @Route("/delete/{formsuccess}", methods={"DELETE"}, name="admin_formsuccess_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}