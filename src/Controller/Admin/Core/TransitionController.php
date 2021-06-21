<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Color;
use App\Entity\Core\Transition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TransitionController
 *
 * Transition management
 *
 * @Route("/admin-%security_token%/{website}/website/transitions", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Color $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TransitionController extends AdminController
{
    protected $class = Transition::class;

    /**
     * Delete Transition
     *
     * @Route("/delete/{transition}", methods={"DELETE"}, name="admin_transition_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}