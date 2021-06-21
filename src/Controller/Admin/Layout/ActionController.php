<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Action;
use App\Form\Manager\Layout\ActionManager;
use App\Form\Type\Layout\Management\ActionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ActionController
 *
 * Layout Action management
 *
 * @Route("/admin-%security_token%/{website}/layouts/actions", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Action $class
 * @property ActionType $formType
 * @property ActionManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionController extends AdminController
{
    protected $class = Action::class;
    protected $formType = ActionType::class;
    protected $formManager = ActionManager::class;

    /**
     * Index Action
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_action_index")
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
     * @Route("/new", methods={"GET", "POST"}, name="admin_action_new")
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
     * @Route("/edit/{action}", methods={"GET", "POST"}, name="admin_action_edit")
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
     * @Route("/show/{action}", methods={"GET"}, name="admin_action_show")
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
     * @Route("/position/{action}", methods={"GET", "POST"}, name="admin_action_position")
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
     * @Route("/delete/{action}", methods={"DELETE"}, name="admin_action_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}