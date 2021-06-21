<?php

namespace App\Controller\Admin\Module\Forum;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Forum\Forum;
use App\Form\Manager\Module\ForumManager;
use App\Form\Type\Module\Forum\ForumType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ForumController
 *
 * Forum Action management
 *
 * @Route("/admin-%security_token%/{website}/forums", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORUM")
 *
 * @property Forum $class
 * @property ForumType $formType
 * @property ForumManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ForumController extends AdminController
{
    protected $class = Forum::class;
    protected $formType = ForumType::class;
    protected $formManager = ForumManager::class;

    /**
     * Index Forum
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_forum_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Forum
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_forum_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Forum
     *
     * @Route("/edit/{forum}", methods={"GET", "POST"}, name="admin_forum_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Forum
     *
     * @Route("/show/{forum}", methods={"GET"}, name="admin_forum_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Forum
     *
     * @Route("/position/{forum}", methods={"GET", "POST"}, name="admin_forum_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Forum
     *
     * @Route("/delete/{forum}", methods={"DELETE"}, name="admin_forum_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}