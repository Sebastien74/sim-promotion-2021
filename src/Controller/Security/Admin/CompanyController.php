<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\Company;
use App\Form\Manager\Security\Admin\CompanyManager;
use App\Form\Type\Security\Admin\CompanyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CompanyController
 *
 * Security Company management
 *
 * @Route("/admin-%security_token%/{website}/security/compagnies", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Company $class
 * @property CompanyType $formType
 * @property CompanyManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CompanyController extends AdminController
{
    protected $class = Company::class;
    protected $formType = CompanyType::class;
    protected $formManager = CompanyManager::class;

    /**
     * Index Company
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_securitycompany_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Company
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_securitycompany_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Company
     *
     * @Route("/edit/{securitycompany}", methods={"GET", "POST"}, name="admin_securitycompany_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->template = 'admin/page/security/company.html.twig';
        return parent::edit($request);
    }

    /**
     * Show Company
     *
     * @Route("/show/{securitycompany}", methods={"GET"}, name="admin_securitycompany_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete Company
     *
     * @Route("/delete/{securitycompany}", methods={"DELETE"}, name="admin_securitycompany_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}