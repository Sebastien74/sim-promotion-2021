<?php

namespace App\Controller\Admin\Information;

use App\Controller\Admin\AdminController;
use App\Entity\Information\Legal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LegalController
 *
 * Information Legal management
 *
 * @Route("/admin-%security_token%/{website}/information/legals", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property Legal $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LegalController extends AdminController
{
    protected $class = Legal::class;

    /**
     * Delete Legal
     *
     * @Route("/delete/{legal}", methods={"DELETE"}, name="admin_legal_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}