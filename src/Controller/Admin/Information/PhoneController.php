<?php

namespace App\Controller\Admin\Information;

use App\Controller\Admin\AdminController;
use App\Entity\Information\Phone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PhoneController
 *
 * Information Phone management
 *
 * @Route("/admin-%security_token%/{website}/information/phones", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Phone $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PhoneController extends AdminController
{
    protected $class = Phone::class;

    /**
     * Delete Phone
     *
     * @Route("/delete/{phone}", methods={"DELETE"}, name="admin_phone_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Position Phone
     *
     * @Route("/position/{phone}", methods={"GET"}, name="admin_phone_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }
}