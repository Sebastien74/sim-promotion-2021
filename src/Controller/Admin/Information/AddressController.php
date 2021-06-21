<?php

namespace App\Controller\Admin\Information;

use App\Controller\Admin\AdminController;
use App\Entity\Information\Address;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AddressController
 *
 * Information Address management
 *
 * @Route("/admin-%security_token%/{website}/information/addresses", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Address $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressController extends AdminController
{
    protected $class = Address::class;

    /**
     * Delete Address
     *
     * @Route("/delete/{address}", methods={"DELETE"}, name="admin_address_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Position Address
     *
     * @Route("/position/{address}", methods={"GET"}, name="admin_address_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }
}