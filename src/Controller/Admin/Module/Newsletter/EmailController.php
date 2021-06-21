<?php

namespace App\Controller\Admin\Module\Newsletter;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newsletter\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EmailController
 *
 * Newsletters Email Action management
 *
 * @Route("/admin-%security_token%/{website}/newsletters/campaigns/emails", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSLETTER")
 *
 * @property Email $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EmailController extends AdminController
{
    protected $class = Email::class;

    /**
     * Index Email
     *
     * @Route("/{campaign}/index", methods={"GET", "POST"}, name="admin_newsletteremail_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Show Email
     *
     * @Route("/{campaign}/show/{newsletteremail}", methods={"GET"}, name="admin_newsletteremail_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Export Email[]
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_newsletteremail_export")
     *
     * {@inheritdoc}
     */
    public function export(Request $request)
    {
        return parent::export($request);
    }

    /**
     * Delete Email
     *
     * @Route("/{campaign}/delete/{newsletteremail}", methods={"DELETE"}, name="admin_newsletteremail_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}