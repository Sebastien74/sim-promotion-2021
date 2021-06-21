<?php

namespace App\Controller\Security\Front;

use App\Controller\Admin\AdminController;
use App\Entity\Security\UserFront;
use App\Form\Manager\Security\Front\UserManager;
use App\Form\Type\Security\Front\UserPasswordType;
use App\Form\Type\Security\Front\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserController
 *
 * Security User management
 *
 * @Route("/admin-%security_token%/{website}/security/users-front", schemes={"%protocol%"})
 * @IsGranted("ROLE_USERS")
 *
 * @property UserFront $class
 * @property UserType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserController extends AdminController
{
    protected $class = UserFront::class;
    protected $formType = UserType::class;
    protected $formManager = UserManager::class;

    /**
     * Index UserFront
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_userfront_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New UserFront
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_userfront_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit UserFront
     *
     * @Route("/edit/{userfront}", methods={"GET", "POST"}, name="admin_userfront_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->template = 'admin/page/security/user-front.html.twig';
        return parent::edit($request);
    }

    /**
     * Edit UserFront password
     *
     * @Route("/password/{userfront}", methods={"GET", "POST"}, name="admin_userfront_password")
     *
     * {@inheritdoc}
     */
    public function password(Request $request)
    {
        $this->entity = $this->entityManager->getRepository($this->class)->find($request->get('userfront'));
        $this->template = 'admin/page/security/password-user-front.html.twig';
        $this->formType = UserPasswordType::class;

        return parent::edit($request);
    }

    /**
     * Show UserFront
     *
     * @Route("/show/{userfront}", methods={"GET"}, name="admin_userfront_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete UserFront
     *
     * @Route("/delete/{userfront}", methods={"DELETE"}, name="admin_userfront_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}