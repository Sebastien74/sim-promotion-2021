<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\GridCol;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * GridColController
 *
 * Layout GridCol management
 *
 * @Route("/admin-%security_token%/{website}/layouts/grid-cols", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property GridCol $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridColController extends AdminController
{
    protected $class = GridCol::class;

    /**
     * Delete GridCol
     *
     * @Route("/delete/{gridcol}", methods={"DELETE"}, name="admin_gridcol_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}