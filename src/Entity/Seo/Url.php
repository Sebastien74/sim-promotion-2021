<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Repository\Seo\UrlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Url
 *
 * @ORM\Table(name="seo_url")
 * @ORM\Entity(repositoryClass=UrlRepository::class)
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Url extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'url'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOnline = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isIndex = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hideInSitemap = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isArchived = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Seo\Seo", inversedBy="url", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $seo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Page", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $indexPage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     */
    private $website;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getIsOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getIsIndex(): ?bool
    {
        return $this->isIndex;
    }

    public function setIsIndex(bool $isIndex): self
    {
        $this->isIndex = $isIndex;

        return $this;
    }

    public function getHideInSitemap(): ?bool
    {
        return $this->hideInSitemap;
    }

    public function setHideInSitemap(bool $hideInSitemap): self
    {
        $this->hideInSitemap = $hideInSitemap;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getSeo(): ?Seo
    {
        return $this->seo;
    }

    public function setSeo(?Seo $seo): self
    {
        $this->seo = $seo;

        return $this;
    }

    public function getIndexPage(): ?Page
    {
        return $this->indexPage;
    }

    public function setIndexPage(?Page $indexPage): self
    {
        $this->indexPage = $indexPage;

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
