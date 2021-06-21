<?php

namespace App\Entity\Translation;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Repository\Translation\i18nRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * i18n
 *
 * @ORM\Table(name="translation_i18n", indexes={
 *     @Index(columns={"title"}, flags={"fulltext"}),
 *     @Index(columns={"introduction"}, flags={"fulltext"}),
 *     @Index(columns={"body"}, flags={"fulltext"})
 * })
 *
 * @ORM\Entity(repositoryClass=i18nRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18n extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'i18n'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $titleForce;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titleAlignment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTitlePosition = 'bottom';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $introduction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $introductionAlignment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bodyAlignment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $targetLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $targetLabel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $targetAlignment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $targetStyle;

    /**
     * @ORM\Column(type="boolean")
     */
    private $newTab = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $externalLink = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $headerTable = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $placeholder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $help;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $error;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $pictogram;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $video;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Page", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $targetPage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
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

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitleForce(): ?int
    {
        return $this->titleForce;
    }

    public function setTitleForce(?int $titleForce): self
    {
        $this->titleForce = $titleForce;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitleAlignment(): ?string
    {
        return $this->titleAlignment;
    }

    public function setTitleAlignment(?string $titleAlignment): self
    {
        $this->titleAlignment = $titleAlignment;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getSubTitlePosition(): ?string
    {
        return $this->subTitlePosition;
    }

    public function setSubTitlePosition(?string $subTitlePosition): self
    {
        $this->subTitlePosition = $subTitlePosition;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getIntroductionAlignment(): ?string
    {
        return $this->introductionAlignment;
    }

    public function setIntroductionAlignment(?string $introductionAlignment): self
    {
        $this->introductionAlignment = $introductionAlignment;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBodyAlignment(): ?string
    {
        return $this->bodyAlignment;
    }

    public function setBodyAlignment(?string $bodyAlignment): self
    {
        $this->bodyAlignment = $bodyAlignment;

        return $this;
    }

    public function getTargetLink(): ?string
    {
        return $this->targetLink;
    }

    public function setTargetLink(?string $targetLink): self
    {
        $this->targetLink = $targetLink;

        return $this;
    }

    public function getTargetLabel(): ?string
    {
        return $this->targetLabel;
    }

    public function setTargetLabel(?string $targetLabel): self
    {
        $this->targetLabel = $targetLabel;

        return $this;
    }

    public function getTargetAlignment(): ?string
    {
        return $this->targetAlignment;
    }

    public function setTargetAlignment(?string $targetAlignment): self
    {
        $this->targetAlignment = $targetAlignment;

        return $this;
    }

    public function getTargetStyle(): ?string
    {
        return $this->targetStyle;
    }

    public function setTargetStyle(?string $targetStyle): self
    {
        $this->targetStyle = $targetStyle;

        return $this;
    }

    public function getNewTab(): ?bool
    {
        return $this->newTab;
    }

    public function setNewTab(bool $newTab): self
    {
        $this->newTab = $newTab;

        return $this;
    }

    public function getExternalLink(): ?bool
    {
        return $this->externalLink;
    }

    public function setExternalLink(bool $externalLink): self
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    public function getHeaderTable(): ?bool
    {
        return $this->headerTable;
    }

    public function setHeaderTable(bool $headerTable): self
    {
        $this->headerTable = $headerTable;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;

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

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): self
    {
        $this->help = $help;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getPictogram(): ?string
    {
        return $this->pictogram;
    }

    public function setPictogram(?string $pictogram): self
    {
        $this->pictogram = $pictogram;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getTargetPage(): ?Page
    {
        return $this->targetPage;
    }

    public function setTargetPage(?Page $targetPage): self
    {
        $this->targetPage = $targetPage;

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