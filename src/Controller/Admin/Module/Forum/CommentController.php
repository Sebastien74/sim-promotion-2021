<?php

namespace App\Controller\Admin\Module\Forum;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Forum\Comment;
use App\Form\Type\Module\Forum\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CommentController
 *
 * Faq Comment Action management
 *
 * @Route("/admin-%security_token%/{website}/forums/comments", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORUM")
 *
 * @property Comment $class
 * @property CommentType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommentController extends AdminController
{
    protected $class = Comment::class;
    protected $formType = CommentType::class;

    /**
     * Index Comment
     *
     * @Route("/{forum}/index", methods={"GET", "POST"}, name="admin_forumcomment_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Comment
     *
     * @Route("/{forum}/new", methods={"GET", "POST"}, name="admin_forumcomment_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Comment
     *
     * @Route("/{forum}/edit/{forumcomment}", methods={"GET", "POST"}, name="admin_forumcomment_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Comment
     *
     * @Route("/{forum}/show/{forumcomment}", methods={"GET"}, name="admin_forumcomment_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Comment
     *
     * @Route("/position/{forumcomment}", methods={"GET", "POST"}, name="admin_forumcomment_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Comment
     *
     * @Route("/delete/{forumcomment}", methods={"DELETE"}, name="admin_forumcomment_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}