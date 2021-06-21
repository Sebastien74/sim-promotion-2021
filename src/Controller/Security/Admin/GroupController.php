<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Entity\Security\Group;
use App\Entity\Security\Role;
use App\Form\Type\Security\Admin\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GroupController
 *
 * Security Group management
 *
 * @Route("/admin-%security_token%/{website}/security/groups", schemes={"%protocol%"})
 * @IsGranted("ROLE_USERS_GROUP")
 *
 * @property Group $class
 * @property GroupType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupController extends AdminController
{
    protected $class = Group::class;
    protected $formType = GroupType::class;

    /**
     * Index Action
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_securitygroup_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_securitygroup_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        $this->entity = new Group();
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $this->entity->addRole($roleRepository->findOneBy(['name' => 'ROLE_USER']));
        $this->entity->addRole($roleRepository->findOneBy(['name' => 'ROLE_ADMIN']));

        return parent::new($request);
    }

    /**
     * Edit Action
     *
     * @Route("/edit/{securitygroup}", methods={"GET", "POST"}, name="admin_securitygroup_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->isAllowed($request);
        return parent::edit($request);
    }

    /**
     * Show Action
     *
     * @Route("/show/{securitygroup}", methods={"GET"}, name="admin_securitygroup_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        $this->isAllowed($request);
        return parent::show($request);
    }

    /**
     * Position Action
     *
     * @Route("/position/{securitygroup}", methods={"GET", "POST"}, name="admin_securitygroup_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        $this->isAllowed($request);
        return parent::position($request);
    }

    /**
     * Delete Action
     *
     * @Route("/delete/{securitygroup}", methods={"DELETE"}, name="admin_securitygroup_delete")
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
        /** @var Group $group */
        $group = $this->entityManager->getRepository(Group::class)->find($request->get('securitygroup'));

        if ($group) {

            $isInternalGroup = false;
            foreach ($group->getRoles() as $role) {
                if ($role->getName() === 'ROLE_INTERNAL') {
                    $isInternalGroup = true;
                    break;
                }
            }

            if ($isInternalGroup) {
                $this->denyAccessUnlessGranted('ROLE_INTERNAL');
            }
        }
    }
}