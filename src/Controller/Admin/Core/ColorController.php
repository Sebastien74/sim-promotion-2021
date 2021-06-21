<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Color;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ColorController
 *
 * Color management
 *
 * @Route("/admin-%security_token%/{website}/website/colors", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Color $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColorController extends AdminController
{
    protected $class = Color::class;

    /**
     * Delete Color
     *
     * @Route("/delete/{color}", methods={"DELETE"}, name="admin_color_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}