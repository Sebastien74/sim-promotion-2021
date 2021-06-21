<?php

namespace App\Controller\Admin\Gdpr;

use App\Controller\Admin\AdminController;
use App\Entity\Gdpr\Cookie;
use App\Form\Manager\Gdpr\CookieManager;
use App\Form\Type\Gdpr\CookieType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CookieController
 *
 * Gdpr Cookie management
 *
 * @Route("/admin-%security_token%/{website}/gdpr/cookies", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Cookie $class
 * @property CookieType $formType
 * @property CookieManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CookieController extends AdminController
{
    protected $class = Cookie::class;
    protected $formType = CookieType::class;
    protected $formManager = CookieManager::class;

    /**
     * Index Gdpr Cookie
     *
     * @Route("/{gdprgroup}/index", methods={"GET", "POST"}, name="admin_gdprcookie_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Gdpr Cookie
     *
     * @Route("/{gdprgroup}/new", methods={"GET", "POST"}, name="admin_gdprcookie_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Gdpr Cookie
     *
     * @Route("/{gdprgroup}/edit/{gdprcookie}", methods={"GET", "POST"}, name="admin_gdprcookie_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Gdpr Cookie
     *
     * @Route("/{gdprgroup}/show/{gdprcookie}", methods={"GET"}, name="admin_gdprcookie_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Gdpr Cookie
     *
     * @Route("/position/{gdprcookie}", methods={"GET", "POST"}, name="admin_gdprcookie_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Gdpr Cookie
     *
     * @Route("/delete/{gdprcookie}", methods={"DELETE"}, name="admin_gdprcookie_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}