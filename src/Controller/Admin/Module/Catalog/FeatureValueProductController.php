<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Module\Catalog\FeatureValueProduct;
use App\Entity\Module\Catalog\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FeatureValueProductController
 *
 * FeatureValueProduct management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/catalogfeaturevalueproduct", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property FeatureValueProduct $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueProductController extends AdminController
{
    protected $class = FeatureValueProduct::class;

    /**
     * Delete FeatureValueProduct
     *
     * @Route("/delete/{catalogfeaturevalueproduct}", methods={"DELETE"}, name="admin_catalogfeaturevalueproduct_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Position FeatureValueProduct
     *
     * @Route("/position/{catalogfeaturevalueproduct}", methods={"GET", "POST"}, name="admin_catalogfeaturevalueproduct_position")
     *
     * {@inheritdoc}
     */
    public function valuePosition(Request $request)
    {
        /** @var FeatureValueProduct $value */
        $value = $this->entityManager->getRepository($this->class)->find($request->get('catalogfeaturevalueproduct'));
        $value->setPosition($request->get('position'));

        $this->entityManager->persist($value);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Position FeatureValueProduct
     *
     * @Route("/feature-position/{product}/{feature}", methods={"GET", "POST"}, name="admin_catalogfeaturevalueproduct_feature_position")
     *
     * {@inheritdoc}
     */
    public function featurePosition(Request $request, Product $product, Feature $feature)
    {
        foreach ($product->getValues() as $value) {
            if ($value->getFeature()->getId() === $feature->getId()) {
                $value->setFeaturePosition($request->get('position'));
                $this->entityManager->persist($value);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}