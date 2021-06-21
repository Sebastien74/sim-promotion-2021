<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\UserCategory;
use App\Form\Type\Security\Admin\UserCategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserCategoryController
 *
 * Security UserCategory management
 *
 * @Route("/admin-%security_token%/{website}/security/users/categories", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property UserCategory $class
 * @property UserCategoryType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserCategoryController extends AdminController
{
    protected $class = UserCategory::class;
    protected $formType = UserCategoryType::class;

    /**
     * Index UserCategory
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_securityusercategory_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New UserCategory
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_securityusercategory_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit UserCategory
     *
     * @Route("/edit/{securityusercategory}", methods={"GET", "POST"}, name="admin_securityusercategory_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show UserCategory
     *
     * @Route("/show/{securityusercategory}", methods={"GET"}, name="admin_securityusercategory_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position UserCategory
     *
     * @Route("/position/{securityusercategory}", methods={"GET", "POST"}, name="admin_securityusercategory_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete UserCategory
     *
     * @Route("/delete/{securityusercategory}", methods={"DELETE"}, name="admin_securityusercategory_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}