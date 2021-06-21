<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MenuFixture
 *
 * Menu Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property string $locale
 * @property array $pages
 * @property array $pagesParams
 * @property User $user
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuFixture
{
    private $entityManager;
    private $translator;
    private $locale;
    private $pages = [];
    private $pagesParams = [];
    private $user;
    private $position = 1;

    /**
     * MenuFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Add Menus
     *
     * @param Website $website
     * @param array $pages
     * @param array $pagesParams
     * @param User|NULL $user
     */
    public function add(Website $website, array $pages, array $pagesParams, User $user = NULL)
    {
        $this->locale = $website->getConfiguration()->getLocale();
        $this->pages = $pages;
        $this->pagesParams = $pagesParams;
        $this->user = $user;

        $this->addMenu($website, $this->translator->trans('Principal', [], 'admin'), 'main');
        $this->addMenu($website, $this->translator->trans('Pied de page', [], 'admin'), 'footer');
    }

    /**
     * Add Menu
     *
     * @param Website $website
     * @param string $adminName
     * @param string $slug
     */
    private function addMenu(Website $website, string $adminName, string $slug)
    {
        $isMain = $slug === 'main';
        $isFooter = $slug === 'footer';

        $menu = new Menu();
        $menu->setAdminName($adminName);
        $menu->setSlug($slug);
        $menu->setTemplate($slug);
        $menu->setIsMain($isMain);
        $menu->setIsFooter($isFooter);
        $menu->setWebsite($website);
        $menu->setPosition($this->position);

        if ($isMain) {
            $menu->setFixedOnScroll(true);
        }

        if ($this->user) {
            $menu->setCreatedBy($this->user);
        }

        $this->entityManager->persist($menu);
        $this->addLinks($menu);
        $this->position++;
    }

    /**
     * Add Link to menu
     *
     * @param Menu $menu
     */
    private function addLinks(Menu $menu)
    {
        $position = 1;

        foreach ($this->pagesParams as $key => $params) {

            $params = (object)$params;
            $pageMenu = $params->menu;

            if ($pageMenu === $menu->getSlug()) {

                /** @var Page $page */
                $page = $this->pages[$params->reference];

                $link = new Link();
                $link->setAdminName($page->getAdminName());
                $link->setMenu($menu);
                $link->setLocale($this->locale);
                $link->setPosition($position);

                $i18n = new i18n();
                $i18n->setTargetPage($page);
                $i18n->setTitle($page->getAdminName());
                $i18n->setLocale($this->locale);
                $i18n->setWebsite($menu->getWebsite());

                $link->setI18n($i18n);

                if ($this->user) {
                    $link->setCreatedBy($this->user);
                    $i18n->setCreatedBy($this->user);
                }

                $this->entityManager->persist($link);
                $this->entityManager->persist($i18n);

                $position++;
            }
        }
    }
}