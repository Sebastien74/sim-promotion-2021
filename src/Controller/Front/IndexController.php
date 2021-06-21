<?php

namespace App\Controller\Front;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Security\UserFront;
use App\Entity\Seo\Url;
use App\Repository\Layout\PageRepository;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * IndexController
 *
 * Front index controller to manage main pages
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IndexController extends FrontController
{
    /**
     * Main front action method
     *
     * @Route("/{url}", name="front_index", defaults={"url": null}, methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param PageRepository $pageRepository
     * @param SeoService $seoService
     * @param string|null $url
     * @param bool $preview
     * @return Response|RedirectResponse
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(
        Request $request,
        PageRepository $pageRepository,
        SeoService $seoService,
        string $url = NULL,
        bool $preview = false)
    {
        $website = $this->getWebsite($request, true);
        $page = $website ? $this->getPage($pageRepository, $website, $request, $preview, $url) : [];
        $requestUri = $request->getRequestUri();

        /** 404 & Redirection */
        if (!$page) {
            throw $this->createNotFoundException($this->translator->trans("Cette page n'existe pas !!", [], 'front'));
        } elseif (is_array($page) && !empty($page['redirection'])) {
            return $this->redirectToRoute('front_index', ['url' => $page['redirection']], 301);
        }
        $url = $page->getUrls()[0];
        if (!$preview && $page->getIsIndex() && !empty($request->getRequestUri()) && $request->getRequestUri() != '/' && !preg_match('/\?*=/', $request->getRequestUri())) {
            return $this->redirectToRoute('front_index', [], 301);
        }

        /** Secure page login redirection */
        if ($page->isSecure() && !$this->isGranted('ROLE_SECURE_PAGE') && !$this->isGranted('IS_IMPERSONATOR') && !$this->getUser() instanceof UserFront
            || $page->isSecure() && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('security_front_login');
        }

        /** Locale request */
        $request->setLocale($url->getLocale());

        /** Get cache response */
        $cache = $this->masterCache($website, $page);
        if ($cache) {
            return $cache;
        }

        return $this->cache($this->getTemplate($website->getConfiguration(), $page), $page, $this->getArguments($website, $seoService, $page, $url));
    }

    /**
     * Preview
     *
     * @Route("/admin-%security_token%/{website}/front/page/preview/{url}", name="front_page_preview", methods={"GET", "POST"}, schemes={"%protocol%"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param Website $website
     * @param Url $url
     * @return Response|RedirectResponse
     */
    public function preview(Request $request, Website $website, Url $url)
    {
        $request->setLocale($url->getLocale());

        return $this->forward('App\Controller\Front\IndexController::view', [
            'url' => $url->getCode(),
            'website' => $website,
            'preview' => true,
        ]);
    }

    /**
     * Get current Page
     *
     * @param PageRepository $pageRepository
     * @param Website $website
     * @param Request $request
     * @param bool $preview
     * @param string|NULL $url
     * @return Page|array|null
     * @throws NonUniqueResultException
     */
    private function getPage(PageRepository $pageRepository, Website $website, Request $request, bool $preview, string $url = NULL)
    {
        return !$url
            ? $pageRepository->findIndex($website, $request->getLocale(), $preview)
            : $pageRepository->findByUrlCodeAndLocale($website, $url, $request->getLocale(), $preview);
    }

    /**
     * Get Page template
     *
     * @param Configuration $configuration
     * @param Page $page
     * @return string
     */
    private function getTemplate(Configuration $configuration, Page $page): string
    {
        $fileSystem = new Filesystem();
        $template = $page->getTemplate();
        $templateDir = 'front/' . $configuration->getTemplate() . '/template/' . $template;
        $templateDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templateDir);
        return $fileSystem->exists($this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $templateDir)
            ? $templateDir : str_replace($template, 'cms.html.twig', $templateDir);
    }

    /**
     * Get Page arguments
     *
     * @param Website $website
     * @param SeoService $seoService
     * @param Page $page
     * @param Url $url
     * @return array
     */
    private function getArguments(Website $website, SeoService $seoService, Page $page, Url $url): array
    {
        $seo = $seoService->execute($url, $page);
        $session = new Session();
        $session->set('front_seo', $seo);

        return [
            'website' => $website,
            'seo' => $seo,
            'url' => $url,
            'templateName' => str_replace('.html.twig', '', $page->getTemplate()),
            'interface' => $this->getInterface(Page::class),
            'thumbConfiguration' => $this->thumbConfiguration($website, Page::class),
            'entity' => $page
        ];
    }
}