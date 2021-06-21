<?php

namespace App\Entity\Module\Catalog;

use App\Entity\BaseEntity;
use App\Repository\Module\Catalog\ListingFeatureValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingFeatureValue
 *
 * @ORM\Table(name="module_catalog_listing_feature_value")
 * @ORM\Entity(repositoryClass=ListingFeatureValueRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingFeatureValue extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'listing';
    protected static $interface = [
        'name' => 'cataloglistingfeature'
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\Listing", inversedBy="featuresValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $listing;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Catalog\FeatureValue")
     * @ORM\JoinColumn(nullable=false)
     */
    private $value;

    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    public function setListing(?Listing $listing): self
    {
        $this->listing = $listing;

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