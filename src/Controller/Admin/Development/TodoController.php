<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Entity\Todo\Todo;
use App\Form\Type\Development\TodoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TodoController
 *
 * Todo management
 *
 * @Route("/admin-%security_token%/{website}/development/todos", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TodoController extends AdminController
{
    protected $class = Todo::class;
    protected $formType = TodoType::class;

    /**
     * Index Todo
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_todo_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Todo
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_todo_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Todo
     *
     * @Route("/edit/{todo}", methods={"GET", "POST"}, name="admin_todo_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Todo
     *
     * @Route("/show/{todo}", methods={"GET"}, name="admin_todo_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Todo
     *
     * @Route("/position/{todo}", methods={"GET", "POST"}, name="admin_todo_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Todo
     *
     * @Route("/delete/{todo}", methods={"DELETE"}, name="admin_todo_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}