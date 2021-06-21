<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\BlockType;
use App\Form\Type\Layout\Management\BlockTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BlockTypeController
 *
 * Layout BlockType management
 *
 * @Route("/admin-%security_token%/{website}/layouts/blocks-types", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property BlockType $class
 * @property BlockTypeType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockTypeController extends AdminController
{
    protected $class = BlockType::class;
    protected $formType = BlockTypeType::class;

    /**
     * Index BlockType
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_blocktype_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New BlockType
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_blocktype_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit BlockType
     *
     * @Route("/edit/{blocktype}", methods={"GET", "POST"}, name="admin_blocktype_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show BlockType
     *
     * @Route("/show/{blocktype}", methods={"GET"}, name="admin_blocktype_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position BlockType
     *
     * @Route("/position/{blocktype}", methods={"GET", "POST"}, name="admin_blocktype_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete BlockType
     *
     * @Route("/delete/{blocktype}", methods={"DELETE"}, name="admin_blocktype_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}