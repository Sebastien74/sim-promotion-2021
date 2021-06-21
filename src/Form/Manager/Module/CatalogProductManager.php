<?php

namespace App\Form\Manager\Module;

use App\Entity\Information\Address;
use App\Entity\Information\Information;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\FeatureValueProduct;
use App\Entity\Module\Catalog\Product;
use App\Entity\Module\Catalog\Video;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CatalogProductManager
 *
 * Manage Product in admin
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogProductManager
{
    private $entityManager;
    private $request;

    /**
     * CatalogProductManager constructor.
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
     * @param Product $product
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function prePersist(Product $product, Website $website, array $interface, Form $form)
    {
        $i18ns = $product->getI18ns();
        $this->setI18ns($website, $i18ns, $product);
        $this->setTitleForce($i18ns);
        $this->setVideos($website, $product);
        $this->setInformation($product);
    }

    /**
     * @preUpdate
     *
     * @param Product $product
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(Product $product, Website $website, array $interface, Form $form)
    {
        $catalogBeforePostId = intval($this->request->request->get('product')['catalogBeforePost']);
        /** @var Catalog $catalogBeforePost */
        $catalogBeforePost = $this->entityManager->getRepository(Catalog::class)->find($catalogBeforePostId);
        $currentCatalog = $product->getCatalog();

        if ($currentCatalog->getId() !== $catalogBeforePost->getId()) {
            $this->setPositions($product, $currentCatalog, $catalogBeforePost);
        }

        $this->setValues($product, $website);
        $this->setVideos($website, $product);

        $this->entityManager->persist($product);
    }

    /**
     * Set i18ns
     *
     * @param Website $website
     * @param Collection $i18ns
     * @param Product $product
     */
    private function setI18ns(Website $website, Collection $i18ns, Product $product)
    {
        if ($i18ns->isEmpty()) {
            foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                $i18n = new i18n();
                $i18n->setLocale($locale);
                $i18n->setWebsite($website);
                $product->addI18n($i18n);
                $this->entityManager->persist($product);
            }
        }
    }

    /**
     * Set title force to H1
     *
     * @param Collection $i18ns
     */
    private function setTitleForce(Collection $i18ns)
    {
        foreach ($i18ns as $i18n) {
            $i18n->setTitleForce(1);
            $this->entityManager->persist($i18n);
        }
    }

    /**
     * Set Videos
     *
     * @param Website $website
     * @param Product $product
     */
    private function setVideos(Website $website, Product $product)
    {
        $position = count($this->entityManager->getRepository(Video::class)->findBy(['product' => $product])) + 1;

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {
            foreach ($product->getVideos() as $video) {

                $existing = false;
                foreach ($video->getI18ns() as $i18n) {
                    if ($i18n->getLocale() === $locale) {
                        $existing = true;
                    }
                }

                if (!$existing) {
                    $i18n = new i18n();
                    $i18n->setLocale($locale);
                    $i18n->setVideo($video->getAdminName());
                    $i18n->setWebsite($website);
                    $video->addI18n($i18n);
                }

                if (!$video->getPosition()) {
                    $video->setPosition($position);
                    $position++;
                }

                $this->entityManager->persist($video);
            }
        }
    }

    /**
     * Set Information
     *
     * @param Product $product
     */
    private function setInformation(Product $product)
    {
        $information = new Information();
        $information->addAddress(new Address());
        $product->addInformation($information);
    }

    /**
     * Set Products positions
     *
     * @param Product $product
     * @param Catalog $currentCatalog
     * @param Catalog $catalogBeforePost
     */
    private function setPositions(Product $product, Catalog $currentCatalog, Catalog $catalogBeforePost)
    {
        foreach ($catalogBeforePost->getProducts() as $beforeProduct) {
            if ($beforeProduct->getId() !== $product->getId() && $beforeProduct->getPosition() > $product->getPosition()) {
                $beforeProduct->setPosition($beforeProduct->getPosition() - 1);
                $this->entityManager->persist($beforeProduct);
            }
        }

        $product->setPosition($currentCatalog->getProducts()->count() + 1);
    }

    /**
     * Set FeatureValueProduct
     *
     * @param Product $product
     * @param Website $website
     */
    private function setValues(Product $product, Website $website)
    {
        $featuresValues = $this->entityManager->getRepository(FeatureValueProduct::class)->findBy(['product' => $product]);
        $position = count($featuresValues) + 1;

        foreach ($product->getValues() as $value) {

            $isCustom = false;
            if ($value->getAdminName() && !$value->getValue()) {
                $isCustom = true;
                $this->setCustomizedValues($value, $website);
            }

            if (!$isCustom && $value->getValue()) {

                $value->setFeature($value->getValue()->getCatalogfeature());
                $this->entityManager->persist($value);

                if (!$value->getId()) {
                    $value->setPosition($position);
                    $position++;
                }

                $this->entityManager->persist($value);

                if (!$value->getProduct()) {
                    $value->setProduct($product);
                }

            } elseif (!$isCustom) {
                $product->removeValue($value);
            }
        }

        $this->setFeaturesPositions($product->getValues());
    }

    /**
     * Set Feature group positions
     *
     * @param Collection $featuresValues
     */
    private function setFeaturesPositions(Collection $featuresValues)
    {
        $lastPosition = 0;
        $positions = [];
        $groups = [];
        foreach ($featuresValues as $featuresValue) {

            $groups[$featuresValue->getFeature()->getId()][] = $featuresValue;

            /** @var FeatureValueProduct $featuresValue */
            if ($featuresValue->getFeaturePosition() > $lastPosition) {
                $lastPosition = $featuresValue->getFeaturePosition();
            }

            if ($featuresValue->getFeaturePosition() > 0) {
                $positions[$featuresValue->getFeature()->getId()] = $featuresValue->getFeaturePosition();
            }
        }

        $lastPosition++;

        foreach ($groups as $featuresValues) {

            $setPosition = false;

            foreach ($featuresValues as $featuresValue) {

                if ($featuresValue->getFeature() instanceof FeatureValue) {

                    if (!empty($positions[$featuresValue->getFeature()->getId()])) {
                        $featuresValue->setFeaturePosition($positions[$featuresValue->getFeature()->getId()]);
                    } elseif ($featuresValue->getFeaturePosition() === 0) {
                        $featuresValue->setFeaturePosition($lastPosition);
                        $setPosition = true;
                    }
                }

                $this->entityManager->persist($featuresValue);
            }

            if ($setPosition) {
                $lastPosition++;
            }
        }
    }

    /**
     * Set FeatureValueProduct has customize
     *
     * @param FeatureValueProduct $value
     * @param Website $website
     */
    private function setCustomizedValues(FeatureValueProduct $value, Website $website)
    {
        if ($value->getFeature() instanceof Feature) {

            $newValue = new FeatureValue();
            $newValue->setIsCustomized(true);
            $newValue->setWebsite($website);
            $newValue->setCatalogfeature($value->getFeature());
            $newValue->setProduct($value->getProduct());
            $newValue->setAdminName($value->getAdminName());
            $newValue->setPosition($value->getFeature()->getValues()->count() + 1);

            foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                $i18n = new i18n();
                $i18n->setTitle($value->getAdminName());
                $i18n->setWebsite($website);
                $i18n->setLocale($locale);
                $newValue->addI18n($i18n);
            }

            $value->setValue($newValue);

            $this->entityManager->persist($newValue);
            $this->entityManager->persist($value);
        }
    }
}