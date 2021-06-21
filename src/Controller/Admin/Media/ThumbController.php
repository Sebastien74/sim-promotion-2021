<?php

namespace App\Controller\Admin\Media;

use App\Controller\Admin\AdminController;
use App\Entity\Media\Thumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ThumbController
 *
 * Media Thumb management
 *
 * @Route("/admin-%security_token%/{website}/medias/thumbs", schemes={"%protocol%"})
 * @IsGranted("ROLE_MEDIA")
 *
 * @property Thumb $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbController extends AdminController
{
    protected $class = Thumb::class;

    /**
     * Delete Thumb
     *
     * @Route("/delete/{thumb}", methods={"DELETE"}, name="admin_thumb_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}