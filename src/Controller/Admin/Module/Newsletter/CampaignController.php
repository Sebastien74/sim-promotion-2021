<?php

namespace App\Controller\Admin\Module\Newsletter;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newsletter\Campaign;
use App\Form\Manager\Module\CampaignManager;
use App\Form\Type\Module\Newsletter\CampaignType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CampaignController
 *
 * Newsletters Campaign Action management
 *
 * @Route("/admin-%security_token%/{website}/newsletters/campaigns", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSLETTER")
 *
 * @property Campaign $class
 * @property CampaignType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CampaignController extends AdminController
{
    protected $class = Campaign::class;
    protected $formType = CampaignType::class;
    protected $formManager = CampaignManager::class;

    /**
     * Index Campaign
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_campaign_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Campaign
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_campaign_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Campaign
     *
     * @Route("/edit/{campaign}", methods={"GET", "POST"}, name="admin_campaign_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Campaign
     *
     * @Route("/show/{campaign}", methods={"GET"}, name="admin_campaign_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Campaign
     *
     * @Route("/position/{campaign}", methods={"GET", "POST"}, name="admin_campaign_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Campaign
     *
     * @Route("/delete/{campaign}", methods={"DELETE"}, name="admin_campaign_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}