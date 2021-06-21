<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Domain;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DomainController
 *
 * Domain management
 *
 * @Route("/admin-%security_token%/{website}/website/domains", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Domain $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainController extends AdminController
{
    protected $class = Domain::class;

    /**
     * Delete Domain
     *
     * @Route("/delete/{domain}", methods={"DELETE"}, name="admin_domain_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}