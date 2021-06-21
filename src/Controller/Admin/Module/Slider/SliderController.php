<?php

namespace App\Controller\Admin\Module\Slider;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Slider\Slider;
use App\Form\Manager\Module\SliderManager;
use App\Form\Type\Module\Slider\SliderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SliderController
 *
 * Slider Action management
 *
 * @Route("/admin-%security_token%/{website}/sliders", schemes={"%protocol%"})
 * @IsGranted("ROLE_SLIDER")
 *
 * @property Slider $class
 * @property SliderType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SliderController extends AdminController
{
    protected $class = Slider::class;
    protected $formType = SliderType::class;
    protected $formManager = SliderManager::class;

    /**
     * Index Slider
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_slider_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Slider
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_slider_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Slider
     *
     * @Route("/edit/{slider}", methods={"GET", "POST"}, name="admin_slider_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Slider
     *
     * @Route("/show/{slider}", methods={"GET"}, name="admin_slider_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Slider
     *
     * @Route("/position/{slider}", methods={"GET", "POST"}, name="admin_slider_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Slider
     *
     * @Route("/delete/{slider}", methods={"DELETE"}, name="admin_slider_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}