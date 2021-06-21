<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Layout\Page;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AddLinkManager
 *
 * Manage admin Link Menu form
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddLinkManager
{
    private $entityManager;
    private $request;
    private $position;

    /**
     * AddLinkManager constructor.
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
     * Add Pages Link in Menu
     *
     * @param array $post
     * @param Menu $menu
     * @param string $locale
     * @param bool $multiple
     */
    public function post(array $post, Menu $menu, string $locale, bool $multiple)
    {
        $pages = [];

        if (!empty($post['page']) && !$multiple) {
            $pages[] = $post['page'];
        } elseif ($multiple) {
            $pages = $post;
        }

        if (!empty($pages)) {

            $this->setPosition($menu, $locale);

            foreach ($pages as $pageId) {

                $page = $this->getPage(intval($pageId));
                $link = $this->addLink($page, $locale, $menu);
                $i18n = $this->addI18n($link, $page, $locale);
                $this->addMediaRelation($link, $locale);

                $this->entityManager->persist($link);
                $this->entityManager->persist($i18n);

                $this->position++;
            }

            $this->entityManager->flush();
        }
    }

    /**
     * set position
     *
     * @param Menu $menu
     * @param string $locale
     */
    private function setPosition(Menu $menu, string $locale)
    {
        $repository = $this->entityManager->getRepository(Link::class);
        $this->position = count($repository->findBy([
                'menu' => $menu,
                'locale' => $locale,
                'level' => 1
            ])) + 1;
    }

    /**
     * Get Page
     *
     * @param int $pageId
     * @return Page|null
     */
    private function getPage(int $pageId)
    {
        $pageRepository = $this->entityManager->getRepository(Page::class);
        return $pageRepository->find(intval($pageId));
    }

    /**
     * Add Link
     *
     * @param Page $page
     * @param string $locale
     * @param Menu $menu
     * @return Link
     */
    private function addLink(Page $page, string $locale, Menu $menu)
    {
        $link = new Link();
        $link->setLocale($locale);
        $link->setAdminName($page->getAdminName());
        $link->setMenu($menu);
        $link->setPosition($this->position);

        return $link;
    }

    /**
     * Add i18n
     *
     * @param Link $link
     * @param Page $page
     * @param string $locale
     * @return i18n
     */
    private function addI18n(Link $link, Page $page, string $locale)
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setWebsite($page->getWebsite());
        $i18n->setTargetPage($page);
        $i18n->setTitle($page->getAdminName());

        $link->setI18n($i18n);

        return $i18n;
    }

    /**
     * Add MediaRelation
     *
     * @param Link $link
     * @param string $locale
     * @return MediaRelation
     */
    private function addMediaRelation(Link $link, string $locale)
    {
        $mediaRelation = new MediaRelation();
        $mediaRelation->setLocale($locale);

        $link->setMediaRelation($mediaRelation);

        return $mediaRelation;
    }
}