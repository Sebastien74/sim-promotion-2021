<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Form\Manager\Core\BaseDuplicateManager;
use App\Form\Manager\Seo\UrlManager;
use App\Repository\Layout\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * PageDuplicateManager
 *
 * Manage admin Page duplication form
 *
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 * @property PageRepository $repository
 * @property LayoutDuplicateManager $layoutDuplicateManager
 * @property UrlManager $urlManager
 * @property LayoutManager $layoutManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageDuplicateManager extends BaseDuplicateManager
{
    protected $entityManager;
    protected $website;
    private $repository;
    private $layoutDuplicateManager;
    private $urlManager;
    private $layoutManager;

    /**
     * PageDuplicateManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LayoutDuplicateManager $layoutDuplicateManager
     * @param UrlManager $urlManager
     * @param LayoutManager $layoutManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LayoutDuplicateManager $layoutDuplicateManager,
        UrlManager $urlManager,
        LayoutManager $layoutManager)
    {
        $this->entityManager = $entityManager;
        $this->layoutDuplicateManager = $layoutDuplicateManager;
        $this->repository = $entityManager->getRepository(Page::class);
        $this->urlManager = $urlManager;
        $this->layoutManager = $layoutManager;
    }

    /**
     * Duplicate Page
     *
     * @param Page $page
     * @param Website $website
     * @param Form $form
     * @throws NonUniqueResultException
     */
    public function execute(Page $page, Website $website, Form $form)
    {
        /** @var Page $pageToDuplicate */
        $pageToDuplicate = $form->get('page')->getData();
        $duplicateToWebsite = $page->getWebsite();

        $session = new Session();
        $session->set('DUPLICATE_TO_WEBSITE', $duplicateToWebsite);

        $this->setPage($pageToDuplicate, $page, $duplicateToWebsite);
        $this->addLayout($page, $pageToDuplicate->getLayout(), $duplicateToWebsite);
        $this->addUrls($page, $duplicateToWebsite);
        $this->addMediaRelations($page, $pageToDuplicate->getMediaRelations());

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        $this->layoutManager->setGridZone($page->getLayout());

        $session->remove('DUPLICATE_TO_WEBSITE');
    }

    /**
     * Set Page
     *
     * @param Page $pageToDuplicate
     * @param Page $page
     * @param Website $website
     */
    private function setPage(Page $pageToDuplicate, Page $page, Website $website)
    {
        $parentPage = $page->getParent();

        $page->setTemplate($pageToDuplicate->getTemplate());
        $page->setWebsite($website);
        $page->setLevel($this->getLevel($parentPage));
        $page->setPosition($this->getPosition($website, $parentPage));
    }

    /**
     * Add Layout
     *
     * @param Page $page
     * @param Layout $layoutToDuplicate
     * @param Website $website
     */
    private function addLayout(Page $page, Layout $layoutToDuplicate, Website $website)
    {
        $layout = new Layout();
        $layout->setPage($page);
        $layout->setAdminName($page->getAdminName());

        $page->setLayout($layout);

        $this->layoutDuplicateManager->addLayout($layout, $layoutToDuplicate, $website);

        $this->entityManager->persist($layout);
        $this->entityManager->persist($page);
    }

    /**
     * Add Url
     *
     * @param Page $page
     * @param Website $website
     * @throws NonUniqueResultException
     */
    private function addUrls(Page $page, Website $website)
    {
        $locales = $website->getConfiguration()->getAllLocales();

        foreach ($locales as $locale) {

            $url = new Url();
            $url->setLocale($locale);

            $this->urlManager->addUrl($url, $website, $page);

            $page->addUrl($url);
        }
    }

    /**
     * Get level
     *
     * @param null|Page $parentPage
     * @return int
     */
    private function getLevel(Page $parentPage = NULL): int
    {
        return $parentPage ? $parentPage->getLevel() + 1 : 1;
    }

    /**
     * Get new position
     *
     * @param Website $website
     * @param null|Page $parentPage
     * @return int
     */
    private function getPosition(Website $website, Page $parentPage = NULL): int
    {
        return $parentPage
            ? count($parentPage->getPages()) + 1
            : count($this->repository->findBy([
                'website' => $website,
                'level' => 1
            ])) + 1;
    }
}