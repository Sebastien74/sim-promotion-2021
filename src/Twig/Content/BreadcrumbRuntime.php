<?php

namespace App\Twig\Content;

use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * BreadcrumbRuntime
 *
 * @property TranslatorInterface $translator
 * @property RouterInterface $router
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BreadcrumbRuntime implements RuntimeExtensionInterface
{
    private $translator;
    private $router;
    private $entityManager;
    private $request;

    /**
     * BreadcrumbRuntime constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Generate breadcrumb
     *
     * @param Page|null $page
     * @param string|null $currentTitle
     * @param null $currentEntity
     * @return null|void
     */
    public function breadcrumb(Page $page = NULL, string $currentTitle = NULL, $currentEntity = NULL)
    {
        if (!$page && !$currentTitle) {
            return NULL;
        }

        $breadcrumbs[] = ['title' => $this->translator->trans('Accueil', [], 'front'), 'url' => $this->router->generate('front_index')];

        if ($page) {

            $indexPage = $this->indexPage($currentEntity);
            $page = $indexPage ? $indexPage : $page;
            $website = $page->getWebsite();
            $items = $this->getBreadcrumb($page, []);
            $locale = $this->request->getLocale();
            $blockRepository = $this->entityManager->getRepository(Block::class);
            $pageRepository = $this->entityManager->getRepository(Page::class);

            foreach ($items as $item) {

                if (!$item->getIsIndex()) {

                    $url = NULL;
                    $seo = NULL;
                    $seoForTitle = NULL;
                    $itemTitle = NULL;

                    if ($item instanceof Page && !$item->getInfill()) {
                        $itemTitle = $blockRepository->findTitleByForceAndLocalePage($item, $locale, 1);
                    }

                    $urls = $item->getUrls();
                    if ($item instanceof Page && $item->getInfill()) {
                        $children = $pageRepository->findBy(['website' => $website, 'parent' => $item], ['position' => 'ASC']);
                        foreach ($urls as $url) {
                            if ($url->getLocale() === $locale) {
                                $seoForTitle = $url->getSeo();
                                break;
                            }
                        }
                        $urls = !empty($children[0]) ? $children[0]->getUrls() : $urls;
                    }

                    foreach ($urls as $url) {
                        if ($url->getLocale() === $locale) {
                            $seo = $url->getSeo();
                            break;
                        }
                    }

                    $seo = $seoForTitle ? $seoForTitle : $seo;

                    if (!empty($seo)) {

                        $breadcrumbMeta = $itemTitle && !$item->getInfill() ? $itemTitle : $seo->getBreadcrumbTitle();
                        $itemTitle = !empty($breadcrumbMeta) ? $breadcrumbMeta : $seo->getMetaTitle();

                        if (!empty($itemTitle)) {
                            $breadcrumbs[] = ['title' => $itemTitle, 'url' => $this->router->generate('front_index', ['url' => $url->getCode()])];
                        } else {
                            $breadcrumbs[] = ['title' => $item->getAdminName(), 'url' => $this->router->generate('front_index', ['url' => $url->getCode()])];
                        }
                    } else {
                        $breadcrumbs[] = ['title' => $item->getAdminName(), 'url' => $this->router->generate('front_index', ['url' => $url->getCode()])];
                    }
                }
            }
        }

        if ($currentTitle) {
            $breadcrumbs[] = ['title' => $currentTitle];
        }

        return $breadcrumbs;
    }

    /**
     * Generate breadcrumb tree
     *
     * @param Page $page
     * @param $items
     * @return array
     */
    public function getBreadcrumb(Page $page, $items)
    {
        $level = $page->getLevel();

        if (!$page->getInfill()) {
            $items[$level] = $page;
            $indexPage = $this->indexPage($page);
            $parent = $indexPage ? $indexPage : $page->getParent();
        }

        if (!empty($parent) && $level > 1) {
            return $this->getBreadcrumb($parent, $items);
        }

        ksort($items);

        return $items;
    }

    /**
     * Get index Page
     *
     * @param mixed $currentEntity
     * @return Page|null
     */
    public function indexPage($currentEntity)
    {
        if (method_exists($currentEntity, 'getUrls')) {
            foreach ($currentEntity->getUrls() as $url) {
                /** @var Url $url */
                if ($url->getLocale() === $this->request->getLocale() && $url->getIndexPage()) {
                    return $url->getIndexPage();
                }
            }
        }
    }
}