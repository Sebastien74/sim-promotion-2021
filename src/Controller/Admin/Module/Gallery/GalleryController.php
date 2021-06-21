<?php

namespace App\Controller\Admin\Module\Gallery;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Gallery\Gallery;
use App\Form\Type\Module\Gallery\GalleryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GalleryController
 *
 * Gallery Action management
 *
 * @Route("/admin-%security_token%/{website}/galleries", schemes={"%protocol%"})
 * @IsGranted("ROLE_GALLERY")
 *
 * @property Gallery $class
 * @property GalleryType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GalleryController extends AdminController
{
    protected $class = Gallery::class;
    protected $formType = GalleryType::class;

    /**
     * Index Gallery
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_gallery_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Gallery
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_gallery_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Gallery
     *
     * @Route("/edit/{gallery}", methods={"GET", "POST"}, name="admin_gallery_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Gallery
     *
     * @Route("/show/{gallery}", methods={"GET"}, name="admin_gallery_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Gallery
     *
     * @Route("/position/{gallery}", methods={"GET", "POST"}, name="admin_gallery_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Gallery
     *
     * @Route("/delete/{gallery}", methods={"DELETE"}, name="admin_gallery_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}