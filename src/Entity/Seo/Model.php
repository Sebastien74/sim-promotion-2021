<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Repository\Seo\ModelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Model
 *
 * @ORM\Table(name="seo_model")
 * @ORM\Entity(repositoryClass=ModelRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Model extends BaseInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $noAfterDash = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaTitleSecond;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $footerDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaOgTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaOgDescription;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $className;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoAfterDash(): ?bool
    {
        return $this->noAfterDash;
    }

    public function setNoAfterDash(bool $noAfterDash): self
    {
        $this->noAfterDash = $noAfterDash;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaTitleSecond(): ?string
    {
        return $this->metaTitleSecond;
    }

    public function setMetaTitleSecond(?string $metaTitleSecond): self
    {
        $this->metaTitleSecond = $metaTitleSecond;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getFooterDescription(): ?string
    {
        return $this->footerDescription;
    }

    public function setFooterDescription(?string $footerDescription): self
    {
        $this->footerDescription = $footerDescription;

        return $this;
    }

    public function getMetaOgTitle(): ?string
    {
        return $this->metaOgTitle;
    }

    public function setMetaOgTitle(?string $metaOgTitle): self
    {
        $this->metaOgTitle = $metaOgTitle;

        return $this;
    }

    public function getMetaOgDescription(): ?string
    {
        return $this->metaOgDescription;
    }

    public function setMetaOgDescription(?string $metaOgDescription): self
    {
        $this->metaOgDescription = $metaOgDescription;

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

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
