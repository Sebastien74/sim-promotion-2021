<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Repository\Module\Catalog\FeatureValueProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeatureValueProduct
 *
 * @ORM\Table(name="module_catalog_product_values")
 * @ORM\Entity(repositoryClass=FeatureValueProductRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueProduct extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'product';
    protected static $interface = [
        'name' => 'catalogfeaturevalueproduct'
    ];

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $featurePosition = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayInArray = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Product", inversedBy="values")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Feature")
     * @ORM\JoinColumn(nullable=true)
     */
    private $feature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\FeatureValue")
     * @ORM\JoinColumn(nullable=true)
     */
    private $value;

    public function getFeaturePosition(): ?int
    {
        return $this->featurePosition;
    }

    public function setFeaturePosition(?int $featurePosition): self
    {
        $this->featurePosition = $featurePosition;

        return $this;
    }

    public function getDisplayInArray(): ?bool
    {
        return $this->displayInArray;
    }

    public function setDisplayInArray(bool $displayInArray): self
    {
        $this->displayInArray = $displayInArray;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getFeature(): ?Feature
    {
        return $this->feature;
    }

    public function setFeature(?Feature $feature): self
    {
        $this->feature = $feature;

        return $this;
    }

    public function getValue(): ?FeatureValue
    {
        return $this->value;
    }

    public function setValue(?FeatureValue $value): self
    {
        $this->value = $value;

        return $this;
    }
}