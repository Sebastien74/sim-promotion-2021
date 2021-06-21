<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\ThumbConfiguration;
use App\Form\Type\Media\ThumbConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ThumbConfigurationController
 *
 * Media ThumbConfiguration management
 *
 * @Route("/admin-%security_token%/{website}/medias/thumbs-configurations", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property ThumbConfiguration $class
 * @property ThumbConfigurationType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbConfigurationController extends AdminController
{
    protected $class = ThumbConfiguration::class;
    protected $formType = ThumbConfigurationType::class;

    /**
     * Index ThumbConfiguration
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_thumbconfiguration_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New ThumbConfiguration
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_thumbconfiguration_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit ThumbConfiguration
     *
     * @Route("/edit/{thumbconfiguration}", name="admin_thumbconfiguration_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show ThumbConfiguration
     *
     * @Route("/show/{thumbconfiguration}", methods={"GET"}, name="admin_thumbconfiguration_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position ThumbConfiguration
     *
     * @Route("/position/{thumbconfiguration}", methods={"GET", "POST"}, name="admin_thumbconfiguration_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete ThumbConfiguration
     *
     * @Route("/delete/{thumbconfiguration}", methods={"DELETE"}, name="admin_thumbconfiguration_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}