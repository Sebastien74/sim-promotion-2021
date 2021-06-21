<?php

namespace App\Controller\Admin\Gdpr;

use App\Controller\Admin\AdminController;
use App\Entity\Gdpr\Group;
use App\Form\Type\Gdpr\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GroupController
 *
 * Gdpr Group management
 *
 * @Route("/admin-%security_token%/{website}/gdpr/groups", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Group $class
 * @property GroupType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupController extends AdminController
{
    protected $class = Group::class;
    protected $formType = GroupType::class;

    /**
     * Index Gdpr Group
     *
     * @Route("/{gdprcategory}/index", methods={"GET", "POST"}, name="admin_gdprgroup_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Gdpr Group
     *
     * @Route("/{gdprcategory}/new", methods={"GET", "POST"}, name="admin_gdprgroup_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Gdpr Group
     *
     * @Route("/{gdprcategory}/edit/{gdprgroup}", methods={"GET", "POST"}, name="admin_gdprgroup_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Gdpr Group
     *
     * @Route("/{gdprcategory}/show/{gdprgroup}", methods={"GET"}, name="admin_gdprgroup_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Gdpr Group
     *
     * @Route("/position/{gdprgroup}", methods={"GET", "POST"}, name="admin_gdprgroup_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Gdpr Group
     *
     * @Route("/delete/{gdprgroup}", methods={"DELETE"}, name="admin_gdprgroup_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}