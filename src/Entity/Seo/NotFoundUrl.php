<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Repository\Seo\NotFoundUrlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotFoundUrl
 *
 * @ORM\Table(name="seo_not_found_url")
 * @ORM\Entity(repositoryClass=NotFoundUrlRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NotFoundUrl extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'notfoundurl'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $uri;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $haveRedirection = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     */
    private $website;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getHaveRedirection(): ?bool
    {
        return $this->haveRedirection;
    }

    public function setHaveRedirection(bool $haveRedirection): self
    {
        $this->haveRedirection = $haveRedirection;

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
