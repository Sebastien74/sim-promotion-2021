<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\CssClass;
use App\Entity\Layout\Zone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CssClassController
 *
 * Layout CSS Zone management
 *
 * @Route("/admin-%security_token%/{website}/layouts/css-classes", schemes={"%protocol%"})
 * @IsGranted("ROLE_EDIT")
 *
 * @property Zone $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CssClassController extends AdminController
{
    protected $class = Zone::class;

    /**
     * Delete CssClass
     *
     * @Route("/delete/{cssclass}", methods={"DELETE"}, name="admin_cssclass_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        $this->class = CssClass::class;
        return parent::delete($request);
    }
}