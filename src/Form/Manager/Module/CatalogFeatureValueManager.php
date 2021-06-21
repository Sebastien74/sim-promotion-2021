<?php

namespace App\Form\Manager\Module;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CatalogFeatureValueManager
 *
 * Manage FeatureValue in admin
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogFeatureValueManager
{
    private $entityManager;
    private $request;

    /**
     * CatalogFeatureValueManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @prePersist
     *
     * @param FeatureValue $featureValue
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function prePersist(FeatureValue $featureValue, Website $website, array $interface, Form $form)
    {
        $this->entityManager->persist($featureValue);
    }

    /**
     * @preUpdate
     *
     * @param FeatureValue $featureValue
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(FeatureValue $featureValue, Website $website, array $interface, Form $form)
    {
        $featureBeforePostId = intval($this->request->request->get('feature_value')['featureBeforePost']);
        $featureBeforePost = $this->entityManager->getRepository(Feature::class)->find($featureBeforePostId);
        $currentFeature = $featureValue->getCatalogfeature();

        if ($currentFeature->getId() !== $featureBeforePost->getId()) {
            $this->setProductsFeature($featureValue);
            $this->setPositions($featureValue, $currentFeature);
        }

        $this->entityManager->persist($featureValue);
    }

    /**
     * Set FeatureValueProduct Product[]
     *
     * @param FeatureValue $featureValue
     */
    private function setProductsFeature(FeatureValue $featureValue)
    {
        $products = $this->entityManager->getRepository(Product::class)->findByValue($featureValue);
        foreach ($products as $product) {
            /* @var Product $product */
            foreach ($product->getValues() as $value) {
                if ($value->getValue()->getId() === $featureValue->getId()) {
                    $value->setFeature($featureValue->getCatalogfeature());
                    $this->entityManager->persist($value);
                }
            }
        }
    }

    /**
     * Set FeatureValue positions
     *
     * @param FeatureValue $featureValue
     * @param Feature $currentFeature
     */
    private function setPositions(FeatureValue $featureValue, Feature $currentFeature)
    {
        foreach ($featureValue->getCatalogfeature()->getValues() as $value) {
            if ($value->getPosition() > $featureValue->getPosition()) {
                $value->setPosition($value->getPosition() - 1);
                $this->entityManager->persist($value);
            }
        }

        $featureValue->setPosition($currentFeature->getValues()->count() + 1);
    }
}