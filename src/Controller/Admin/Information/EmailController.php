<?php

namespace App\Controller\Admin\Information;

use App\Controller\Admin\AdminController;
use App\Entity\Information\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EmailController
 *
 * Information Phone management
 *
 * @Route("/admin-%security_token%/{website}/information/emails", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Email $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EmailController extends AdminController
{
    protected $class = Email::class;

    /**
     * Delete Email
     *
     * @Route("/delete/{email}", methods={"DELETE"}, name="admin_email_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Position Email
     *
     * @Route("/position/{email}", methods={"GET"}, name="admin_email_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }
}