<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\ThumbAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ThumbActionController
 *
 * Media ThumbAction management
 *
 * @Route("/admin-%security_token%/{website}/medias/thumbs-actions", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property ThumbAction $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbActionController extends AdminController
{
    protected $class = ThumbAction::class;

    /**
     * Delete ThumbAction
     *
     * @Route("/delete/{thumbaction}", methods={"DELETE"}, name="admin_thumbaction_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}