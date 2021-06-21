<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Entity\Gdpr\Category;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\CssClass;
use App\Entity\Layout\Page;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\TranslationDomain;
use App\Repository\Core\ConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Configuration
 *
 * @ORM\Table(name="core_configuration")
 * @ORM\Entity(repositoryClass=ConfigurationRepository::class)
 * @ORM\Cache(usage = "READ_ONLY")
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Configuration extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $interface = [
        'name' => 'configuration'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $template = 'default';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $locale = 'fr';

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $locales = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $onlineLocales = ['fr'];

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasDefault = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fullWidth = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onlineStatus = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seoStatus = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mediasCategoriesStatus = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $duplicateMediasStatus = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fullCache = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fullCacheDev = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $preloader = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scrollTopBtn = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $breadcrumb = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $subNavigation = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $collapsedAdminTrees = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adminAdvertising = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $cacheExpiration = 120; // minutes

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $gdprFrequency = 1095;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $charset = 'UTF-8';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $emailsDev = ['sebastien@felix-creation.fr'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $emailsSupport = ['sebastien@felix-creation.fr', 'support@felix-creation.fr'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ipsDev = ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ipsCustomer = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ipsBan = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $frontFonts = ['roboto'];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $adminFonts = [];

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $adminTheme = 'felix';

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $hoverTheme = 'box9';

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Website", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $website;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Domain", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @Assert\Valid()
     */
    private $domains;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Color", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"category"="ASC"})
     * @Assert\Valid()
     */
    private $colors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Transition", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"isActive"="DESC", "adminName"="ASC"})
     * @Assert\Valid()
     */
    private $transitions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Layout\CssClass", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"name"="ASC"})
     * @Assert\Valid()
     */
    private $cssClasses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Icon", mappedBy="configuration", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"filename"="ASC"})
     * @Assert\Valid()
     */
    private $icons;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Media\MediaRelation", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC", "category"="ASC"})
     * @ORM\JoinTable(name="core_configuration_media_relations",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="relation_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $mediaRelations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gdpr\Category", mappedBy="configuration", fetch="EXTRA_LAZY")
     * @Assert\Valid()
     */
    private $gdprcategories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Core\Module", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="core_configurations_modules",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $modules;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Layout\BlockType", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="core_configurations_block_types",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="block_type_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $blockTypes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\TranslationDomain", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="core_configuration_translation_domains",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="domain_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $transDomains;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Layout\Page", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="core_configuration_pages",
     *      joinColumns={@ORM\JoinColumn(name="configuration_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     * )
     * @ORM\OrderBy({"adminName"="ASC"})
     */
    private $pages;

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $this->domains = new ArrayCollection();
        $this->gdprcategories = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->transitions = new ArrayCollection();
        $this->mediaRelations = new ArrayCollection();
        $this->modules = new ArrayCollection();
        $this->icons = new ArrayCollection();
        $this->blockTypes = new ArrayCollection();
        $this->cssClasses = new ArrayCollection();
        $this->transDomains = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

	/**
	 * Get all IPS
	 *
	 * @param array $ipsDev
	 * @param array $ipsCustomer
	 * @return array
	 */
    public function getAllIPS(array $ipsDev = [], array $ipsCustomer = []): ?array
    {
		$this->ipsDev = !empty($ipsDev) ? $ipsDev : $this->ipsDev;
		$this->ipsCustomer = !empty($ipsCustomer) ? $ipsCustomer : $this->ipsCustomer;

        $ipsDev = [];
        if (is_array($this->ipsDev)) {
            foreach ($this->ipsDev as $ip) {
                $matches = explode(',', $ip);
                foreach ($matches as $match) {
                    $ipsDev[] = $match;
                }
            }
        }

        $ipsCustomer = [];
        if (is_array($this->ipsCustomer)) {
            foreach ($this->ipsCustomer as $ip) {
                $matches = explode(',', $ip);
                foreach ($matches as $match) {
                    $ipsCustomer[] = $match;
                }
            }
        }

        $result = array_unique(array_merge($ipsDev, $ipsCustomer));

        return $result ? $result : ['::1', '127.0.0.1', 'fe80::1', '77.158.35.74', '176.135.112.19'];
    }

    /**
     * Get all Locales
     *
     * @return array
     */
    public function getAllLocales(): ?array
    {
        $allLocales = [$this->locale];
        if (!empty($this->locales)) {
            $allLocales = array_merge($allLocales, $this->locales);
        }
        sort($allLocales);

        return array_unique($allLocales);
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
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

    public function getLocales(): ?array
    {
        return $this->locales;
    }

    public function setLocales(?array $locales): self
    {
        $this->locales = $locales;

        return $this;
    }

    public function getOnlineLocales(): ?array
    {
        return $this->onlineLocales;
    }

    public function setOnlineLocales(?array $onlineLocales): self
    {
        $this->onlineLocales = $onlineLocales;

        return $this;
    }

    public function getHasDefault(): ?bool
    {
        return $this->hasDefault;
    }

    public function setHasDefault(bool $hasDefault): self
    {
        $this->hasDefault = $hasDefault;

        return $this;
    }

    public function getFullWidth(): ?bool
    {
        return $this->fullWidth;
    }

    public function setFullWidth(bool $fullWidth): self
    {
        $this->fullWidth = $fullWidth;

        return $this;
    }

    public function getOnlineStatus(): ?bool
    {
        return $this->onlineStatus;
    }

    public function setOnlineStatus(bool $onlineStatus): self
    {
        $this->onlineStatus = $onlineStatus;

        return $this;
    }

    public function getSeoStatus(): ?bool
    {
        return $this->seoStatus;
    }

    public function setSeoStatus(bool $seoStatus): self
    {
        $this->seoStatus = $seoStatus;

        return $this;
    }

    public function getMediasCategoriesStatus(): ?bool
    {
        return $this->mediasCategoriesStatus;
    }

    public function setMediasCategoriesStatus(bool $mediasCategoriesStatus): self
    {
        $this->mediasCategoriesStatus = $mediasCategoriesStatus;

        return $this;
    }

    public function getDuplicateMediasStatus(): ?bool
    {
        return $this->duplicateMediasStatus;
    }

    public function setDuplicateMediasStatus(bool $duplicateMediasStatus): self
    {
        $this->duplicateMediasStatus = $duplicateMediasStatus;

        return $this;
    }

    public function getFullCache(): ?bool
    {
        return $this->fullCache;
    }

    public function setFullCache(bool $fullCache): self
    {
        $this->fullCache = $fullCache;

        return $this;
    }

    public function getFullCacheDev(): ?bool
    {
        return $this->fullCacheDev;
    }

    public function setFullCacheDev(bool $fullCacheDev): self
    {
        $this->fullCacheDev = $fullCacheDev;

        return $this;
    }

    public function getPreloader(): ?bool
    {
        return $this->preloader;
    }

    public function setPreloader(bool $preloader): self
    {
        $this->preloader = $preloader;

        return $this;
    }

    public function getScrollTopBtn(): ?bool
    {
        return $this->scrollTopBtn;
    }

    public function setScrollTopBtn(bool $scrollTopBtn): self
    {
        $this->scrollTopBtn = $scrollTopBtn;

        return $this;
    }

    public function getBreadcrumb(): ?bool
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(bool $breadcrumb): self
    {
        $this->breadcrumb = $breadcrumb;

        return $this;
    }

    public function getSubNavigation(): ?bool
    {
        return $this->subNavigation;
    }

    public function setSubNavigation(bool $subNavigation): self
    {
        $this->subNavigation = $subNavigation;

        return $this;
    }

    public function getCollapsedAdminTrees(): ?bool
    {
        return $this->collapsedAdminTrees;
    }

    public function setCollapsedAdminTrees(bool $collapsedAdminTrees): self
    {
        $this->collapsedAdminTrees = $collapsedAdminTrees;

        return $this;
    }

    public function getAdminAdvertising(): ?bool
    {
        return $this->adminAdvertising;
    }

    public function setAdminAdvertising(bool $adminAdvertising): self
    {
        $this->adminAdvertising = $adminAdvertising;

        return $this;
    }

    public function getCacheExpiration(): ?int
    {
        return $this->cacheExpiration;
    }

    public function setCacheExpiration(?int $cacheExpiration): self
    {
        $this->cacheExpiration = $cacheExpiration;

        return $this;
    }

    public function getGdprFrequency(): ?int
    {
        return $this->gdprFrequency;
    }

    public function setGdprFrequency(?int $gdprFrequency): self
    {
        $this->gdprFrequency = $gdprFrequency;

        return $this;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(?string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getEmailsDev(): ?array
    {
        return $this->emailsDev;
    }

    public function setEmailsDev(?array $emailsDev): self
    {
        $this->emailsDev = $emailsDev;

        return $this;
    }

    public function getEmailsSupport(): ?array
    {
        return $this->emailsSupport;
    }

    public function setEmailsSupport(?array $emailsSupport): self
    {
        $this->emailsSupport = $emailsSupport;

        return $this;
    }

    public function getIpsDev(): ?array
    {
        return $this->ipsDev;
    }

    public function setIpsDev(?array $ipsDev): self
    {
        $this->ipsDev = $ipsDev;

        return $this;
    }

    public function getIpsCustomer(): ?array
    {
        return $this->ipsCustomer;
    }

    public function setIpsCustomer(?array $ipsCustomer): self
    {
        $this->ipsCustomer = $ipsCustomer;

        return $this;
    }

    public function getIpsBan(): ?array
    {
        return $this->ipsBan;
    }

    public function setIpsBan(?array $ipsBan): self
    {
        $this->ipsBan = $ipsBan;

        return $this;
    }

    public function getFrontFonts(): ?array
    {
        return $this->frontFonts;
    }

    public function setFrontFonts(?array $frontFonts): self
    {
        $this->frontFonts = $frontFonts;

        return $this;
    }

    public function getAdminFonts(): ?array
    {
        return $this->adminFonts;
    }

    public function setAdminFonts(?array $adminFonts): self
    {
        $this->adminFonts = $adminFonts;

        return $this;
    }

    public function getAdminTheme(): ?string
    {
        return $this->adminTheme;
    }

    public function setAdminTheme(?string $adminTheme): self
    {
        $this->adminTheme = $adminTheme;

        return $this;
    }

    public function getHoverTheme(): ?string
    {
        return $this->hoverTheme;
    }

    public function setHoverTheme(?string $hoverTheme): self
    {
        $this->hoverTheme = $hoverTheme;

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

    /**
     * @return Collection|Domain[]
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains[] = $domain;
            $domain->setConfiguration($this);
        }

        return $this;
    }

    public function removeDomain(Domain $domain): self
    {
        if ($this->domains->contains($domain)) {
            $this->domains->removeElement($domain);
            // set the owning side to null (unless already changed)
            if ($domain->getConfiguration() === $this) {
                $domain->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Color[]
     */
    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(Color $color): self
    {
        if (!$this->colors->contains($color)) {
            $this->colors[] = $color;
            $color->setConfiguration($this);
        }

        return $this;
    }

    public function removeColor(Color $color): self
    {
        if ($this->colors->contains($color)) {
            $this->colors->removeElement($color);
            // set the owning side to null (unless already changed)
            if ($color->getConfiguration() === $this) {
                $color->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transition[]
     */
    public function getTransitions(): Collection
    {
        return $this->transitions;
    }

    public function addTransition(Transition $transition): self
    {
        if (!$this->transitions->contains($transition)) {
            $this->transitions[] = $transition;
            $transition->setConfiguration($this);
        }

        return $this;
    }

    public function removeTransition(Transition $transition): self
    {
        if ($this->transitions->contains($transition)) {
            $this->transitions->removeElement($transition);
            // set the owning side to null (unless already changed)
            if ($transition->getConfiguration() === $this) {
                $transition->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CssClass[]
     */
    public function getCssClasses(): Collection
    {
        return $this->cssClasses;
    }

    public function addCssClass(CssClass $cssClass): self
    {
        if (!$this->cssClasses->contains($cssClass)) {
            $this->cssClasses[] = $cssClass;
            $cssClass->setConfiguration($this);
        }

        return $this;
    }

    public function removeCssClass(CssClass $cssClass): self
    {
        if ($this->cssClasses->contains($cssClass)) {
            $this->cssClasses->removeElement($cssClass);
            // set the owning side to null (unless already changed)
            if ($cssClass->getConfiguration() === $this) {
                $cssClass->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Icon[]
     */
    public function getIcons(): Collection
    {
        return $this->icons;
    }

    public function addIcon(Icon $icon): self
    {
        if (!$this->icons->contains($icon)) {
            $this->icons[] = $icon;
            $icon->setConfiguration($this);
        }

        return $this;
    }

    public function removeIcon(Icon $icon): self
    {
        if ($this->icons->contains($icon)) {
            $this->icons->removeElement($icon);
            // set the owning side to null (unless already changed)
            if ($icon->getConfiguration() === $this) {
                $icon->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MediaRelation[]
     */
    public function getMediaRelations(): Collection
    {
        return $this->mediaRelations;
    }

    public function addMediaRelation(MediaRelation $mediaRelation): self
    {
        if (!$this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations[] = $mediaRelation;
        }

        return $this;
    }

    public function removeMediaRelation(MediaRelation $mediaRelation): self
    {
        if ($this->mediaRelations->contains($mediaRelation)) {
            $this->mediaRelations->removeElement($mediaRelation);
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getGdprcategories(): Collection
    {
        return $this->gdprcategories;
    }

    public function addGdprcategory(Category $gdprcategory): self
    {
        if (!$this->gdprcategories->contains($gdprcategory)) {
            $this->gdprcategories[] = $gdprcategory;
            $gdprcategory->setConfiguration($this);
        }

        return $this;
    }

    public function removeGdprcategory(Category $gdprcategory): self
    {
        if ($this->gdprcategories->contains($gdprcategory)) {
            $this->gdprcategories->removeElement($gdprcategory);
            // set the owning side to null (unless already changed)
            if ($gdprcategory->getConfiguration() === $this) {
                $gdprcategory->setConfiguration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Module[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules[] = $module;
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        if ($this->modules->contains($module)) {
            $this->modules->removeElement($module);
        }

        return $this;
    }

    /**
     * @return Collection|BlockType[]
     */
    public function getBlockTypes(): Collection
    {
        return $this->blockTypes;
    }

    public function addBlockType(BlockType $blockType): self
    {
        if (!$this->blockTypes->contains($blockType)) {
            $this->blockTypes[] = $blockType;
        }

        return $this;
    }

    public function removeBlockType(BlockType $blockType): self
    {
        if ($this->blockTypes->contains($blockType)) {
            $this->blockTypes->removeElement($blockType);
        }

        return $this;
    }

    /**
     * @return Collection|TranslationDomain[]
     */
    public function getTransDomains(): Collection
    {
        return $this->transDomains;
    }

    public function addTransDomain(TranslationDomain $transDomain): self
    {
        if (!$this->transDomains->contains($transDomain)) {
            $this->transDomains[] = $transDomain;
        }

        return $this;
    }

    public function removeTransDomain(TranslationDomain $transDomain): self
    {
        if ($this->transDomains->contains($transDomain)) {
            $this->transDomains->removeElement($transDomain);
        }

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
        }

        return $this;
    }
}