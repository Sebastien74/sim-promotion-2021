<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\User;
use App\Form\Manager\Security\Admin\UserManager;
use App\Form\Type\Security\Admin\UserPasswordType;
use App\Form\Type\Security\Admin\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UserController
 *
 * Security User management
 *
 * @Route("/admin-%security_token%/{website}/security/users", schemes={"%protocol%"})
 * @IsGranted("ROLE_USERS")
 *
 * @property User $class
 * @property UserType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserController extends AdminController
{
    protected $class = User::class;
    protected $formType = UserType::class;
    protected $formManager = UserManager::class;

    /**
     * Index User
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_user_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New User
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_user_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit User
     *
     * @Route("/edit/{user}", methods={"GET", "POST"}, name="admin_user_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->isAllowed($request);
        $this->template = 'admin/page/security/user.html.twig';
        return parent::edit($request);
    }

    /**
     * Edit User password
     *
     * @Route("/password/{user}", methods={"GET", "POST"}, name="admin_user_password")
     *
     * {@inheritdoc}
     */
    public function password(Request $request)
    {
        $this->isAllowed($request);
        $this->template = 'admin/page/security/password.html.twig';
        $this->formType = UserPasswordType::class;
        return parent::edit($request);
    }

    /**
     * Show User
     *
     * @Route("/show/{user}", methods={"GET"}, name="admin_user_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        $this->isAllowed($request);
        return parent::show($request);
    }

    /**
     * Delete User
     *
     * @Route("/delete/{user}", methods={"DELETE"}, name="admin_user_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        $this->isAllowed($request);
        return parent::delete($request);
    }

    /**
     * Check if current User is allowed to edit internal entities
     *
     * @param Request $request
     */
    private function isAllowed(Request $request)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($request->get('user'));

        $isInternalUser = false;
        foreach ($user->getRoles() as $role) {
            if ($role === 'ROLE_INTERNAL') {
                $isInternalUser = true;
                break;
            }
        }

        if ($isInternalUser) {
            $this->denyAccessUnlessGranted('ROLE_INTERNAL');
        }
    }
}