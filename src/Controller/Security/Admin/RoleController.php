<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\Role;
use App\Form\Manager\Security\Admin\RoleManager;
use App\Form\Type\Security\Admin\RoleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RoleController
 *
 * Security Role management
 *
 * @Route("/admin-%security_token%/{website}/security/roles", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Role $class
 * @property RoleType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RoleController extends AdminController
{
    protected $class = Role::class;
    protected $formType = RoleType::class;
    protected $formManager = RoleManager::class;

    /**
     * Index Action
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_securityrole_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Action
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_securityrole_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Action
     *
     * @Route("/edit/{securityrole}", methods={"GET", "POST"}, name="admin_securityrole_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Action
     *
     * @Route("/show/{securityrole}", methods={"GET"}, name="admin_securityrole_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Action
     *
     * @Route("/position/{securityrole}", methods={"GET", "POST"}, name="admin_securityrole_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Action
     *
     * @Route("/delete/{securityrole}", methods={"DELETE"}, name="admin_securityrole_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}