<?php

namespace App\Controller\Admin\Module\Newscast;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newscast\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CommentController
 *
 * Newscast Comment Action management
 *
 * @Route("/admin-%security_token%/{website}/newscasts/comments", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSCAST")
 *
 * @property Comment $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommentController extends AdminController
{
    protected $class = Comment::class;

    /**
     * Index Comments
     *
     * @Route("/index", methods={"GET", "POST"}, defaults={"join": null}, name="admin_comment_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Show Comments
     *
     * @Route("/show/{comment}", methods={"GET"}, name="admin_comment_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }
}