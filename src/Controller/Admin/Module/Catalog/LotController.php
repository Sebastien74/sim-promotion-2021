<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Lot;
use App\Entity\Module\Catalog\Video;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LotController
 *
 * Catalog Lot management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/lots", schemes={"%protocol%"})
 * @IsGranted("ROLE_REAL_ESTATE_PROGRAM")
 *
 * @property Lot $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LotController extends AdminController
{
    protected $class = Lot::class;

    /**
     * Position Lot
     *
     * @Route("/position/{cataloglot}", methods={"GET", "POST"}, name="admin_cataloglot_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        /** @var Lot $lot */
        $lot = $this->entityManager->getRepository($this->class)->find($request->get('cataloglot'));
        $lot->setPosition($request->get('position'));

        $this->entityManager->persist($lot);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Delete Lot
     *
     * @Route("/delete/{cataloglot}", methods={"DELETE"}, name="admin_cataloglot_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}