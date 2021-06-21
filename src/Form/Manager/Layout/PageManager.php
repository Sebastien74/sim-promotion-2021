<?php

namespace App\Form\Manager\Layout;

use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * PageManager
 *
 * Manage admin Page form
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageManager
{
    private $entityManager;
    private $request;

    /**
     * PageManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * @prePersist
     *
     * @param Page $page
     * @param Website $website
     */
    public function prePersist(Page $page, Website $website)
    {
        $this->post($page, $website);
        $this->setPageInTree($page, $website);
    }

    /**
     * @preUpdate
     *
     * @param Page $page
     * @param Website $website
     */
    public function preUpdate(Page $page, Website $website)
    {
        $this->post($page, $website);
    }

    /**
     * Global post
     *
     * @param Page $page
     * @param Website $website
     */
    private function post(Page $page, Website $website)
    {
        $inMenu = isset($this->request->get('page')['inMenu']);

        if ($inMenu) {
            $this->addToMenu($page, $website);
        }

        $this->setIndex($page, $website);
    }

    /**
     * Add to main menu
     *
     * @param Page $page
     * @param Website $website
     */
    private function addToMenu(Page $page, Website $website)
    {
        /** @var Menu $mainMenu */
        $mainMenu = $this->entityManager->getRepository(Menu::class)->findOneBy([
            'isMain' => true,
            'website' => $website
        ]);

        if ($mainMenu) {

            $position = count($this->entityManager->getRepository(Link::class)->findBy([
                    'level' => 1,
                    'menu' => $mainMenu
                ])) + 1;

            $configuration = $website->getConfiguration();
            $defaultLocale = $configuration->getLocale();

            foreach ($configuration->getAllLocales() as $locale) {

                $link = new Link();
                $link->setAdminName($page->getAdminName());
                $link->setMenu($mainMenu);
                $link->setLocale($locale);
                $link->setPosition($position);

                $i18n = new i18n();
                $i18n->setTargetPage($page);
                $i18n->setLocale($locale);
                $i18n->setWebsite($website);

                if ($locale === $defaultLocale) {
                    $i18n->setTitle($page->getAdminName());
                }

                $link->setI18n($i18n);

                $this->entityManager->persist($link);
                $this->entityManager->persist($i18n);
            }
        }
    }

    /**
     * Set Page position
     *
     * @param Page $page
     * @param Website $website
     */
    private function setPageInTree(Page $page, Website $website)
    {
        $position = count($this->entityManager->getRepository(Page::class)->findForTreePosition($website, $page)) + 1;
        $page->setPosition($position);
        $level = $page->getParent() instanceof Page ? $page->getParent()->getLevel() + 1 : 1;
        $page->setLevel($level);
    }

    /**
     * Set index Page
     *
     * @param Page $page
     * @param Website $website
     */
    private function setIndex(Page $page, Website $website)
    {
        $existing = $this->entityManager->getRepository(Page::class)->findOneBy([
            'website' => $website,
            'isIndex' => true,
        ]);

        if ($existing && $existing->getId() !== $page->getId() && $page->getIsIndex()) {
            $existing->setIsIndex(false);
            $this->entityManager->persist($existing);
        }
    }
}