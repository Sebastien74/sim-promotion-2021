<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Media\MediaRelation;
use App\Repository\Seo\SeoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seo
 *
 * @ORM\Table(name="seo")
 * @ORM\Entity(repositoryClass=SeoRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Seo extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'seo'
    ];

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
    private $breadcrumbTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $metadata;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $footerDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaCanonical;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaOgTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $metaOgDescription;

    /**
     * @ORM\Column(name="meta_og_twitter_card", type="string", length=255, nullable=true)
     */
    private $metaOgTwitterCard = "summary";

    /**
     * @ORM\Column(name="meta_og_twitter_handle", type="string", length=255, nullable=true)
     */
    private $metaOgTwitterHandle;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Seo\Url", mappedBy="seo", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $url;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="media_relation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $mediaRelation;

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

    public function getBreadcrumbTitle(): ?string
    {
        return $this->breadcrumbTitle;
    }

    public function setBreadcrumbTitle(?string $breadcrumbTitle): self
    {
        $this->breadcrumbTitle = $breadcrumbTitle;

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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorType(): ?string
    {
        return $this->authorType;
    }

    public function setAuthorType(?string $authorType): self
    {
        $this->authorType = $authorType;

        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setMetadata(?string $metadata): self
    {
        $this->metadata = $metadata;

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

    public function getMetaCanonical(): ?string
    {
        return $this->metaCanonical;
    }

    public function setMetaCanonical(?string $metaCanonical): self
    {
        $this->metaCanonical = $metaCanonical;

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

    public function getMetaOgTwitterCard(): ?string
    {
        return $this->metaOgTwitterCard;
    }

    public function setMetaOgTwitterCard(?string $metaOgTwitterCard): self
    {
        $this->metaOgTwitterCard = $metaOgTwitterCard;

        return $this;
    }

    public function getMetaOgTwitterHandle(): ?string
    {
        return $this->metaOgTwitterHandle;
    }

    public function setMetaOgTwitterHandle(?string $metaOgTwitterHandle): self
    {
        $this->metaOgTwitterHandle = $metaOgTwitterHandle;

        return $this;
    }

    public function getUrl(): ?Url
    {
        return $this->url;
    }

    public function setUrl(?Url $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getMediaRelation(): ?MediaRelation
    {
        return $this->mediaRelation;
    }

    public function setMediaRelation(?MediaRelation $mediaRelation): self
    {
        $this->mediaRelation = $mediaRelation;

        return $this;
    }
}
