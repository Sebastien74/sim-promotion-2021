<?php

namespace App\Controller\Admin\Module\Tab;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Tab\Content;
use App\Form\Type\Module\Tab\ContentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ContentController
 *
 * Tab Content Action management
 *
 * @Route("/admin-%security_token%/{website}/tabs/contents", schemes={"%protocol%"})
 * @IsGranted("ROLE_TAB")
 *
 * @property Content $class
 * @property ContentType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContentController extends AdminController
{
    protected $class = Content::class;
    protected $formType = ContentType::class;

    /**
     * Contents tree
     *
     * @Route("/{tab}/tree", methods={"GET", "POST"}, name="admin_tabcontent_tree")
     *
     * {@inheritdoc}
     */
    public function tree(Request $request)
    {
        return parent::tree($request);
    }

    /**
     * New Content
     *
     * @Route("/{tab}/new", methods={"GET", "POST"}, name="admin_tabcontent_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Content
     *
     * @Route("/{tab}/edit/{tabcontent}", methods={"GET", "POST"}, name="admin_tabcontent_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Content
     *
     * @Route("/{tab}/show/{tabcontent}", methods={"GET"}, name="admin_tabcontent_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Delete Content
     *
     * @Route("/delete/{tabcontent}", methods={"DELETE"}, name="admin_tabcontent_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}