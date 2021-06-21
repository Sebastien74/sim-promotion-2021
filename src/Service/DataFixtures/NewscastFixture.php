<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Listing;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Module\Newscast\Teaser;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Service\Content\LayoutGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NewscastFixture
 *
 * Newscast Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property LayoutGeneratorService $layoutGenerator
 * @property Factory $faker
 * @property Website $website
 * @property array $pagesParams
 * @property User $user
 * @property string $locale
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastFixture
{
    const LIMIT = 4;

    private $entityManager;
    private $translator;
    private $layoutGenerator;
    private $faker;
    private $website;
    private $pagesParams = [];
    private $user;
    private $locale;

    /**
     * ColorFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LayoutGeneratorService $layoutGenerator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, LayoutGeneratorService $layoutGenerator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->layoutGenerator = $layoutGenerator;
    }

    /**
     * Add News
     *
     * @param Website $website
     * @param array $pagesParams
     * @param User|NULL $user
     * @throws \Exception
     */
    public function add(Website $website, array $pagesParams, User $user = NULL)
    {
        $this->faker = Factory::create();
        $this->website = $website;
        $this->pagesParams = $pagesParams;
        $this->user = $user;
        $this->locale = $website->getConfiguration()->getLocale();

        $category = $this->generateCategory();
        $this->generateTeaser($category);

        for ($i = 0; $i < self::LIMIT; $i++) {

            $title = $this->faker->text(30);

            $newscast = new Newscast();
            $newscast->setAdminName($title);
            $newscast->setPublicationStart(new \DateTime(sprintf('-%d days', rand(1, 100))));
            $newscast->setCategory($category);
            $newscast->setWebsite($website);
            $newscast->setPosition($i + 1);

            if ($this->user) {
                $newscast->setCreatedBy($this->user);
            }

            $this->generateI18n($title, $newscast);
            $this->generateUrl($newscast);
        };
    }

    /**
     * Generate Category
     *
     * @return Category
     */
    private function generateCategory(): Category
    {
        $category = new Category();
        $category->setAdminName($this->translator->trans('Principale', [], 'admin'));
        $category->setIsDefault(true);
        $category->setWebsite($this->website);

        if ($this->user) {
            $category->setCreatedBy($this->user);
        }

        $this->entityManager->persist($category);

        $this->addListing($category);
        $this->generateLayout($category);

        return $category;
    }

    /**
     * Generate Listing
     *
     * @param Category $category
     */
    private function addListing(Category $category)
    {
        $listing = new Listing();
        $listing->addCategory($category);
        $listing->setAdminName($this->translator->trans('Principal', [], 'admin'));
        $listing->setLargeFirst(true);
        $listing->setScrollInfinite(true);
        $listing->setWebsite($this->website);
        $listing->setSlug('main');

        if ($this->user) {
            $listing->setCreatedBy($this->user);
        }

        $this->entityManager->persist($listing);
    }

    /**
     * Generate i18n
     *
     * @param string $title
     * @param Newscast $newscast
     */
    private function generateI18n(string $title, Newscast $newscast)
    {
        $i18n = new i18n();
        $i18n->setLocale($this->locale);
        $i18n->setWebsite($this->website);
        $i18n->setTitle($title);
        $i18n->setBody($this->faker->text(150));
        $i18n->setIntroduction($this->faker->text(600));

        if ($this->user) {
            $i18n->setCreatedBy($this->user);
        }

        $this->entityManager->persist($i18n);

        $newscast->addI18n($i18n);
    }

    /**
     * Generate Url
     *
     * @param Newscast $newscast
     */
    private function generateUrl(Newscast $newscast)
    {
        $url = new Url();
        $url->setCode(Urlizer::urlize($newscast->getAdminName()));
        $url->setLocale($this->locale);
        $url->setIsOnline(true);
        $url->setWebsite($this->website);

        if ($this->user) {
            $url->setCreatedBy($this->user);
        }

        $newscast->addUrl($url);

        $this->entityManager->persist($newscast);
    }

    /**
     * Generate Layout
     *
     * @param Category $category
     */
    private function generateLayout(Category $category)
    {
        $layout = $this->layoutGenerator->addLayout($this->website, [
            'adminName' => $this->translator->trans('Fiche actualité principale', [], 'admin'),
            'slug' => 'main-category',
            'newscastcategory' => $category
        ]);

        /** Title */
        $zoneEntitled = $this->layoutGenerator->addZone($layout, ['fullSize' => true, 'paddingTop' => 'pt-0', 'paddingBottom' => 'pb-0']);
        $col = $this->layoutGenerator->addCol($zoneEntitled, ['size' => 12, 'paddingRight' => 'pe-0', 'paddingLeft' => 'ps-0']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-titleheader']);

        /** Content */
        $zoneContent = $this->layoutGenerator->addZone($layout, ['fullSize' => false, 'paddingTop' => NULL, 'paddingBottom' => NULL]);
        /** Content column one */
        $col = $this->layoutGenerator->addCol($zoneContent, ['size' => 6]);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-date']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-intro']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-body']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-link']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-back-button', 'marginTop' => 'mt-lg']);
        /** Content column two */
        $col = $this->layoutGenerator->addCol($zoneContent, ['size' => 6]);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-video']);
        $this->layoutGenerator->addBlock($col, ['blockType' => 'layout-slider']);

        $category->setLayout($layout);
    }

    /**
     * Generate Teaser
     *
     * @param Category $category
     */
    private function generateTeaser(Category $category)
    {
        $teaser = new Teaser();
        $teaser->setAdminName($this->translator->trans('Principal', [], 'admin'));
        $teaser->setWebsite($this->website);
        $teaser->setSlug('main');

        if ($this->user) {
            $teaser->setCreatedBy($this->user);
        }

        $teaser->addCategory($category);

        $this->entityManager->persist($teaser);
    }
}