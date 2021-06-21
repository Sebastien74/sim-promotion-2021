<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Form as FormEntity;
use App\Entity\Module\Map\Address;
use App\Entity\Module\Map\Map;
use App\Entity\Module\Map\Point;
use App\Entity\Module\Newscast\Listing;
use App\Entity\Module\Newscast\Teaser;
use App\Entity\Module\Slider\Slider;
use App\Entity\Core\Website;
use App\Entity\Layout as Layout;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Security\User;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Form\Manager\Layout\LayoutManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PageFixture
 *
 * Page Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property LayoutManager $layoutManager
 * @property KernelInterface $kernel
 * @property Website $website
 * @property User $user
 * @property string $locale
 * @property array $pages
 * @property int $layoutPosition
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageFixture
{
    private $entityManager;
    private $translator;
    private $layoutManager;
    private $kernel;
    private $website;
    private $user;
    private $locale;
    private $pages = [];
    private $layoutPosition = 1;

    /**
     * PageFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LayoutManager $layoutManager
     * @param KernelInterface $kernel
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        LayoutManager $layoutManager,
        KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->layoutManager = $layoutManager;
        $this->kernel = $kernel;
    }

    /**
     * Add Pages
     *
     * @param Website $website
     * @param array $pagesParams
     * @param User|null $user
     * @return array
     * @throws Exception
     */
    public function add(Website $website, array $pagesParams, User $user = NULL): array
    {
        $this->website = $website;
        $this->user = $user;
        $this->locale = $website->getConfiguration()->getLocale();
        $this->layoutPosition = count($this->entityManager->getRepository(Layout\Layout::class)->findByWebsite($this->website)) + 1;

        foreach ($pagesParams as $key => $pageParams) {
            $params = (object)$pageParams;
            $layout = $this->addLayoutPage($params);
            $page = $this->generatePage($layout, $params, ($key + 1));
            $layout->setPage($page);
            $this->entityManager->persist($layout);
        }

        return $this->pages;
    }

    /**
     * Generate Page
     *
     * @param Layout\Layout $layout
     * @param mixed $params
     * @param int $position
     * @return Layout\Page
     */
    private function generatePage(Layout\Layout $layout, $params, int $position): Layout\Page
    {
        $page = new Layout\Page();
        $page->setAdminName($params->name);
        $page->setWebsite($this->website);
        $page->setIsIndex($params->isIndex);
        $page->setLayout($layout);
        $page->setTemplate($params->template . '.html.twig');
        $page->setPosition($position);
        $page->setDeletable($params->deletable);
        $page->setSlug($params->reference);

        if (!$params->deletable) {
            $page->setInfill(true);
        }

        if ($this->user) {
            $page->setCreatedBy($this->user);
        }

        $this->entityManager->persist($page);
        $this->pages[$params->reference] = $page;

        $this->generateUrl($page, $params->urlIsIndex);

        return $page;
    }

    /**
     * Generate Url
     *
     * @param Layout\Page $page
     * @param bool $isIndex
     */
    private function generateUrl(Layout\Page $page, bool $isIndex)
    {
        $url = new Url();
        $url->setCode(Urlizer::urlize($page->getAdminName()));
        $url->setLocale($this->locale);
        $url->setWebsite($this->website);
        $url->setIsIndex($isIndex);
        $url->setHideInSitemap(!$isIndex);
        $url->setIsOnline(true);

        if (!empty($this->user)) {
            $url->setCreatedBy($this->user);
        }

        $page->addUrl($url);

        $this->entityManager->persist($page);
        $this->entityManager->flush();
    }

    /**
     * Generate Layout Page
     *
     * @param mixed $params
     * @return Layout\Layout
     * @throws Exception
     */
    private function addLayoutPage($params): Layout\Layout
    {
        $layout = $this->addLayout($params->name);

        /** Header */
        if ($params->reference != 'home') {
            $zone = $this->addZone($layout, 1, true);
            $col = $this->addCol($zone);
            $this->addHeader($col, $params->name);
        }

        if ($params->reference == 'home') {
            $this->addHomeLayout($layout);
        } elseif ($params->reference == 'news') {
            $this->addNewsLayout($layout);
        } elseif ($params->reference == 'sitemap') {
            $this->addSitemapLayout($layout);
        } elseif ($params->reference == 'contact') {
            $this->addContactLayout($layout);
        }
//        elseif($params->template == 'legacy') {
//            $this->addLegacyLayout($layout, $params);
//        }

        $this->entityManager->persist($layout);
        $this->entityManager->flush();

        $this->layoutManager->setGridZone($layout);

        return $layout;
    }

    /**
     * Generate Layout
     *
     * @param string $adminName
     * @return Layout\Layout
     */
    private function addLayout(string $adminName): Layout\Layout
    {
        $layout = new Layout\Layout();
        $layout->setWebsite($this->website);
        $layout->setAdminName($adminName);
        $layout->setPosition($this->layoutPosition);
        $layout->setWebsite($this->website);

        if (!empty($this->user)) {
            $layout->setCreatedBy($this->user);
        }

        $this->layoutPosition++;

        return $layout;
    }

    /**
     * Add Zone
     *
     * @param Layout\Layout $layout
     * @param int $position
     * @param bool $fullSize
     * @param bool $noPadding
     * @return Layout\Zone
     */
    private function addZone(Layout\Layout $layout, int $position, bool $fullSize = false, bool $noPadding = false): Layout\Zone
    {
        $zone = new Layout\Zone();
        $zone->setFullSize($fullSize);
        $zone->setPosition($position);

        if ($noPadding) {
            $zone->setPaddingTop('pt-0');
            $zone->setPaddingBottom('pb-0');
        }

        if (!empty($this->user)) {
            $zone->setCreatedBy($this->user);
        }

        $layout->addZone($zone);

        return $zone;
    }

    /**
     * Add Col
     *
     * @param Layout\Zone $zone
     * @param int $position
     * @param int $size
     * @return Layout\Col
     */
    private function addCol(Layout\Zone $zone, int $position = 1, int $size = 12): Layout\Col
    {
        $col = new Layout\Col();
        $col->setPosition($position);
        $col->setSize($size);

        $zone->addCol($col);

        if (!empty($this->user)) {
            $col->setCreatedBy($this->user);
        }

        return $col;
    }

    /**
     * Add Block
     *
     * @param Layout\Col $col
     * @param string $adminName
     * @return Layout\Block
     */
    private function addHeader(Layout\Col $col, string $adminName): Layout\Block
    {
        $col->setPaddingRight('pe-0');
        $col->setPaddingLeft('ps-0');

        $i18n = new i18n();
        $i18n->setTitle($adminName);
        $i18n->setLocale($this->locale);
        $i18n->setWebsite($this->website);
        $i18n->setTitleForce(1);

        $zone = $col->getZone();
        $zone->setPaddingTop('pt-0');
        $zone->setPaddingBottom('pb-0');

        if (!empty($this->user)) {
            $i18n->setCreatedBy($this->user);
        }

        $block = $this->addBlock($col, 'titleheader');
        $block->addI18n($i18n);

        return $block;
    }

    /**
     * Add Block
     *
     * @param Layout\Col $col
     * @param string|null $blockTypeSlug
     * @param string|null $actionSlug
     * @param int|null $actionFilter
     * @param int $position
     * @return Layout\Block
     */
    private function addBlock(Layout\Col $col, string $blockTypeSlug = NULL, string $actionSlug = NULL, int $actionFilter = NULL, int $position = 1): Layout\Block
    {
        $block = new Layout\Block();
        $block->setPosition($position);
        $block->setPaddingLeft('ps-0');
        $block->setPaddingRight('pe-0');

        if (!empty($this->user)) {
            $block->setCreatedBy($this->user);
        }

        $col->addBlock($block);

        $this->addAction($block, $blockTypeSlug, $actionSlug, $actionFilter);

        return $block;
    }

    /**
     * Add Home Layout
     *
     * @param Layout\Layout $layout
     */
    private function addHomeLayout(Layout\Layout $layout)
    {
        $slider = new Slider();
        $slider->setAdminName($this->translator->trans("Carrousel page d'accueil"));
        $slider->setWebsite($this->website);
        $slider->setSlug('main-home');

        $media = $this->entityManager->getRepository(Media::class)->findOneBy([
            'website' => $this->website,
            'category' => 'titleheader'
        ]);

        $mediaRelation = new MediaRelation();
        $mediaRelation->setLocale($this->locale);
        $mediaRelation->setMedia($media);

        $slider->addMediaRelation($mediaRelation);

        $this->entityManager->persist($slider);
        $this->entityManager->flush();

        $carouselZone = $this->addZone($layout, 1, true, true);
        $carouselCol = $this->addCol($carouselZone);
        $carouselCol->setPaddingRight('pe-0');
        $carouselCol->setPaddingLeft('ps-0');
        $this->addBlock($carouselCol, 'core-action', 'slider-view', $slider->getId());

        /** @var Teaser $teaser */
        $teaser = $this->entityManager->getRepository(Teaser::class)->findOneBy(['website' => $this->website]);
        $teaserZone = $this->addZone($layout, 2);
        $teaserCol = $this->addCol($teaserZone);
        $this->addBlock($teaserCol, 'core-action', 'newscast-teaser', $teaser->getId());
    }

    /**
     * Add News Layout
     *
     * @param Layout\Layout $layout
     */
    private function addNewsLayout(Layout\Layout $layout)
    {
        /** @var Listing $listing */
        $listing = $this->entityManager->getRepository(Listing::class)->findOneBy(['website' => $this->website]);
        $zone = $this->addZone($layout, 2, true, true);

        $col = $this->addCol($zone);
        $col->setPaddingRight('pe-0');
        $col->setPaddingLeft('ps-0');

        $this->addBlock($col, 'core-action', 'newscast-index', $listing->getId());
    }

    /**
     * Add Sitemap Layout
     *
     * @param Layout\Layout $layout
     */
    private function addSitemapLayout(Layout\Layout $layout)
    {
        $zone = $this->addZone($layout, 2);
        $col = $this->addCol($zone);
        $this->addBlock($col, 'core-action', 'sitemap-view');
    }

    /**
     * Add Contact Layout
     *
     * @param Layout\Layout $layout
     * @throws Exception
     */
    private function addContactLayout(Layout\Layout $layout)
    {
        $zone = $this->addZone($layout, 2);

        $this->addForm($zone);
        $this->addInformation($zone);

        $this->addMap($layout);
    }

    /**
     * Add Form to contact Page
     *
     * @param Layout\Zone $zone
     * @throws Exception
     */
    private function addForm(Layout\Zone $zone)
    {
        $adminName = $this->translator->trans('Formulaire de contact', [], 'admin');

        $form = new FormEntity\Form();
        $form->setWebsite($this->website);
        $form->setAdminName($adminName);
        $form->setSlug('contact');

        $configuration = new FormEntity\Configuration();
        $configuration->setSecurityKey(crypt(random_bytes(10), 'rl'));
        $form->setConfiguration($configuration);

        if (!empty($this->user)) {
            $form->setCreatedBy($this->user);
        }

        $formLayout = $this->addLayout($adminName);
        $zoneLayout = $this->addZone($formLayout, 1, false, true);

        $colLeft = $this->addCol($zoneLayout, 1, 6);
        $name = $this->addBlock($colLeft, 'form-text');
        $this->addFieldConfiguration($name, 'Nom', 'Saisissez votre nom');
        $email = $this->addBlock($colLeft, 'form-email', NULL, NULL, 2);
        $this->addFieldConfiguration($email, 'Email', 'Saisissez votre email');

        $colRight = $this->addCol($zoneLayout, 2, 6);
        $firstName = $this->addBlock($colRight, 'form-text');
        $this->addFieldConfiguration($firstName, 'Prénom', 'Saisissez votre prénom');
        $phone = $this->addBlock($colRight, 'form-phone', NULL, NULL, 2);
        $this->addFieldConfiguration($phone, 'Téléphone', 'Saisissez votre téléphone');

        $colBottom = $this->addCol($zoneLayout, 3);
        $message = $this->addBlock($colBottom, 'form-textarea');
        $this->addFieldConfiguration($message, 'Message', 'Saisissez votre message');
        $gdpr = $this->addBlock($colBottom, 'form-gdpr', NULL, NULL, 2);
        $this->addFieldConfiguration($gdpr, 'RGPD', "En soumettant ce formulaire, vous acceptez que les informations saisies soient [utilisées, exploitées, traitées] pour permettre de [vous recontacter, pour vous envoyer la newsletter, dans le cadre de la relation commerciale qui découle de cette demande de devis].");
        $submit = $this->addBlock($colBottom, 'form-submit', NULL, NULL, 3);
        $this->addFieldConfiguration($submit, 'Envoyer', 'Saisissez votre message');

        $form->setLayout($formLayout);

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $this->layoutManager->setGridZone($formLayout);

        $col = $this->addCol($zone, 1, 8);
        $this->addBlock($col, 'core-action', 'form-view', $form->getId());
    }

    /**
     * Add contact information to contact Page
     *
     * @param Layout\Zone $zone
     * @throws Exception
     */
    private function addInformation(Layout\Zone $zone)
    {
        $col = $this->addCol($zone, 2, 4);
        $this->addBlock($col, 'core-action', 'information-view');
    }

    /**
     * Add Map to contact Page
     *
     * @param Layout\Layout $layout
     */
    private function addMap(Layout\Layout $layout)
    {
        $map = new Map();
        $map->setAdminName($this->translator->trans('Carte page de contact', [], 'admin'));
        $map->setWebsite($this->website);
        $map->setIsDefault(true);
        $map->setSlug('contact');

        $address = new Address();
        $address->setLatitude(45.899247);
        $address->setLongitude(6.129384);
        $address->setName('Agence Félix');
        $address->setAddress('4BIS Avenue du Pont de Tasset');
        $address->setZipCode('74960');
        $address->setCity('Cran-Gevrier / Annecy');
        $address->setDepartment('Haute-Savoie');
        $address->setCountry('FR');
        $address->setGoogleMapUrl('https://www.google.com/maps/place/Agence+F%C3%A9lix/@45.911696,6.099096,15z/data=!4m2!3m1!1s0x0:0x944c714ef3a6988f?sa=X&ved=2ahUKEwjU89vM7LDmAhUEAWMBHZ2uBNQQ_BIwCnoECA0QCA');

        $point = new Point();
        $point->setAdminName($this->translator->trans('Point principal', [], 'admin'));
        $point->setMarker('/uploads/' . $this->website->getUploadDirname() . '/marker-blue.svg');
        $point->setAddress($address);

        $map->addPoint($point);

        $this->entityManager->persist($map);
        $this->entityManager->flush();

        $zone = $this->addZone($layout, 3, true, true);
        $col = $this->addCol($zone);
        $col->setPaddingRight('pe-0');
        $col->setPaddingLeft('ps-0');

        $this->addBlock($col, 'core-action', 'map-view', $map->getId());
    }

    /**
     * Add Legacy Layout
     *
     * @param Layout\Layout $layout
     * @param $params
     */
    private function addLegacyLayout(Layout\Layout $layout, $params)
    {
        $zone = $this->addZone($layout, 2);
        $col = $this->addCol($zone);
        $block = $this->addBlock($col, 'text');

        $finder = new Finder();
        $dirname = $this->kernel->getProjectDir() . '/bin/data/fixtures/page/';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $finder->files()->in($dirname)->name($params->reference . '.html.twig');

        foreach ($finder as $file) {
            $i18n = new i18n();
            $i18n->setLocale($this->locale);
            $i18n->setWebsite($this->website);
            $i18n->setBody($file->getContents());
            $block->addI18n($i18n);
            break;
        }
    }

    /**
     * Add Action
     *
     * @param Layout\Block $block
     * @param string|null $blockTypeSlug
     * @param string|null $actionSlug
     * @param int|null $actionFilter
     */
    private function addAction(Layout\Block $block, string $blockTypeSlug = NULL, string $actionSlug = NULL, int $actionFilter = NULL)
    {
        if ($blockTypeSlug) {
            $blockType = $this->entityManager->getRepository(Layout\BlockType::class)->findOneBy(['slug' => $blockTypeSlug]);
            $block->setBlockType($blockType);
        }

        if ($actionSlug) {
            $action = $this->entityManager->getRepository(Layout\Action::class)->findOneBy(['slug' => $actionSlug]);
            $block->setAction($action);
        }

        if ($actionFilter) {
            $actionI18n = new Layout\ActionI18n();
            $actionI18n->setLocale($this->locale);
            $actionI18n->setBlock($block);
            $actionI18n->setActionFilter($actionFilter);
            $block->addActionI18n($actionI18n);
        }
    }

    /**
     * Add FieldConfiguration
     *
     * @param Layout\Block $block
     * @param string $label
     * @param string|null $placeholder
     * @param bool $required
     */
    private function addFieldConfiguration(Layout\Block $block, string $label, string $placeholder = NULL, bool $required = true)
    {
        $blockTypeSlug = $block->getBlockType()->getSlug();

        $i18n = new i18n();
        $i18n->setLocale($this->locale);
        $i18n->setWebsite($this->website);

        if ($blockTypeSlug !== 'form-gdpr') {
            $i18n->setTitle($label);
            $i18n->setPlaceholder($placeholder);
        }

        $configuration = new Layout\FieldConfiguration();
        $configuration->setRequired($required);
        $configuration->setBlock($block);

        if ($blockTypeSlug === 'form-gdpr') {

            $configuration->setExpanded(true);
            $configuration->setMultiple(true);

            $valueI18n = new i18n();
            $valueI18n->setLocale($this->locale);
            $valueI18n->setWebsite($this->website);
            $valueI18n->setIntroduction($placeholder);
            $valueI18n->setBody(true);

            $value = new Layout\FieldValue();
            $value->setAdminName($placeholder);
            $value->addI18n($valueI18n);

            $configuration->addFieldValue($value);
        }

        $block->setAdminName($label);
        $block->addI18n($i18n);
        $block->setFieldConfiguration($configuration);

        $this->entityManager->persist($block);
    }
}