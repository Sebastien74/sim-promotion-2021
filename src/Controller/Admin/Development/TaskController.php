<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Entity\Todo\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * QuestionController
 *
 * Task management
 *
 * @Route("/admin-%security_token%/{website}/development/todos/tasks", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Task $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TaskController extends AdminController
{
    protected $class = Task::class;

    /**
     * Delete Task
     *
     * @Route("/delete/{task}", methods={"DELETE"}, name="admin_task_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Position Task
     *
     * @Route("/position/{task}", methods={"GET"}, name="admin_task_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }
}