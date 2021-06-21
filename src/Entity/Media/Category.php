<?php

namespace App\Entity\Media;

use App\Entity\BaseEntity;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Repository\Media\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="media_category")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Category extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'mediacategory'
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Module", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $module;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

        return $this;
    }
}
