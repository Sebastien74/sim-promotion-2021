<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\ListingFeatureValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ListingFeatureController
 *
 * Catalog ListingFeature management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/listings/features", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property ListingFeatureValue $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingFeatureController extends AdminController
{
    protected $class = ListingFeatureValue::class;

    /**
     * Delete ListingFeature
     *
     * @Route("/delete/{cataloglistingfeature}", methods={"DELETE"}, name="admin_cataloglistingfeature_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}