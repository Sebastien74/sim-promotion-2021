<?php

namespace App\Controller\Admin\Translation;

use App\Controller\Admin\AdminController;
use App\Form\Manager\Translation\UnitManager;
use App\Form\Type\Translation\AddTranslationType;
use App\Repository\Core\WebsiteRepository;
use App\Repository\Translation\TranslationDomainRepository;
use App\Service\Translation\Extractor;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * TranslationController
 *
 * Translation management
 *
 * @Route("/admin-%security_token%/{website}/translations", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationController extends AdminController
{
    /**
     * New Translation
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_translation_new")
     *
     * @param Request $request
     * @return string|RedirectResponse|Response
     */
    public function newTranslation(Request $request)
    {
        $form = $this->createForm(AddTranslationType::class, NULL, [
            'action' => $this->generateUrl('admin_translation_new', ['website' => $this->getWebsite($request)->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->subscriber->get(UnitManager::class)->addUnit($form, $this->getWebsite($request));
            return new JsonResponse(['success' => true]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(['html' => $this->renderView('admin/core/new.html.twig', ['form' => $form->createView()])]);
        }

        return $this->cache('admin/core/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Search edit Translations
     *
     * @Route("/search-edit", methods={"GET"}, name="admin_translation_search_edit")
     * @param Request $request
     * @param TranslationDomainRepository $domainRepository
     * @return string|Response
     */
    public function searchEdit(Request $request, TranslationDomainRepository $domainRepository)
    {
        $this->disableProfiler();
        return $this->cache('admin/page/translation/translations.html.twig', [
            'domains' => $domainRepository->findBySearch($request->get('search'))
        ]);
    }

    /**
     * Data fixtures yaml parser
     *
     * @Route("/data-fixtures-parser", methods={"GET"}, name="admin_translation_data_fixtures_parser")
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function dataFixturesParser(Request $request): RedirectResponse
    {
        $filesystem = new Filesystem();
        $fixturesDirname = $this->kernel->getProjectDir() . '/bin/data/translations/';
        $fixturesDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fixturesDirname);
        $parserDirname = $fixturesDirname . 'parser';

        if ($filesystem->exists($parserDirname)) {
            $finder = new Finder();
            $finder->in($parserDirname)->name('*.yaml')->name('*.yml');
            foreach ($finder as $file) {
                if ($file->getType() === 'file' && !preg_match('/undefined/', $file->getFilename())) {
                    $matches = explode('.', str_replace(['.yaml', '.yml'], '', $file->getFilename()));
                    $locale = end($matches);
                    $translationFileName = $fixturesDirname . 'translations.' . $locale . '.yaml';
                    if(preg_match('/entity_/', $file->getFilename())) {
                        $translationFileName = $file->getFilename() !== 'entity_+intl-icu.' . $locale . '.yaml' ? $fixturesDirname . str_replace(['+intl-icu', '.yml'], ['', '.yaml'], $file->getFilename()) : NULL;
                    }
                    if($translationFileName) {
                        $newValues = Yaml::parseFile($file->getRealPath());
                        $existingValues = $filesystem->exists($translationFileName) ? Yaml::parseFile($translationFileName) : [];
                        if (is_array($newValues)) {
                            foreach ($newValues as $keyName => $value) {
                                $keyName = str_replace('__', '', $keyName);
                                if (empty($existingValues[$keyName]) && $value && $value !== $keyName && !is_numeric($value)
                                    && !preg_match('/url-/', $keyName) && !preg_match('/http/', $keyName) && !preg_match('/http/', $value)) {
                                    $existingValues[$keyName] = str_replace('__', '', $value);
                                }
                            }
                        }
                        ksort($existingValues);
                        $yaml = Yaml::dump($existingValues);
                        file_put_contents($translationFileName, $yaml);
                    }
                }
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Extract translations
     *
     * @Route("/extract/{locale}", methods={"GET"}, name="admin_translation_extract", options={"expose"=true})
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @param Extractor $extractor
     * @param string $locale
     * @return JsonResponse
     * @throws Exception
     */
    public function extract(Request $request, WebsiteRepository $websiteRepository, Extractor $extractor, string $locale): JsonResponse
    {
        $website = $websiteRepository->find($request->get('website'));
        $configuration = $website->getConfiguration();

        if ($locale === $configuration->getLocale()) {
            $extractor->extractEntities($website, $configuration->getLocale(), $configuration->getAllLocales());
        }

        foreach ($configuration->getAllLocales() as $locale) {
            $extractor->extract($locale);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Translations progress
     *
     * @Route("/progress", methods={"GET"}, name="admin_translation_progress", options={"expose"=true})
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @param Extractor $extractor
     * @return JsonResponse
     */
    public function progress(Request $request, WebsiteRepository $websiteRepository, Extractor $extractor): JsonResponse
    {
        $domainName = $request->get('domain');
        $website = $websiteRepository->find($request->get('website'));
        $locales = $website->getConfiguration()->getAllLocales();

        $yaml = $extractor->findYaml($locales);
        if (!empty($domainName) && !empty($yaml[$domainName])) {
            $translations[$domainName] = $yaml[$domainName];
        } else {
            $translations = $yaml;
        }

        return new JsonResponse(['html' => $this->renderView('admin/page/translation/progress.html.twig', [
            'translations' => $translations,
            'domainName' => $request->get('domain')
        ])]);
    }

    /**
     * Generate translation
     *
     * @Route("/generate/{locale}/{domain}/{keyName}/{content}", methods={"GET"}, defaults={"keyName"=NULL, "content"=NULL}, name="admin_translation_generate", options={"expose"=true})
     *
     * @param Request $request
     * @param Extractor $extractor
     * @param WebsiteRepository $websiteRepository
     * @param string $locale
     * @param string $domain
     * @param string|null $keyName
     * @param string|null $content
     * @return Response
     */
    public function generate(
        Request $request,
        Extractor $extractor,
        WebsiteRepository $websiteRepository,
        string $locale,
        string $domain,
        string $keyName = NULL,
        string $content = NULL)
    {
        $website = $websiteRepository->find($request->get('website'));
        $defaultLocale = $website->getConfiguration()->getLocale();
        $extractor->generateTranslation($defaultLocale, $locale, urldecode($domain), urldecode($content), urldecode($keyName));

        return new JsonResponse(['success' => true]);
    }

    /**
     * Generate translations file
     *
     * @Route("/generate/files", methods={"GET"}, name="admin_translation_generate_files", options={"expose"=true})
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @param Extractor $extractor
     * @return JsonResponse
     */
    public function generateFiles(Request $request, WebsiteRepository $websiteRepository, Extractor $extractor): JsonResponse
    {
        $website = $websiteRepository->find($request->get('website'));
        $extractor->initFiles($website->getConfiguration()->getAllLocales());
        return new JsonResponse(['success' => true]);
    }

    /**
     * Clear cache
     *
     * @Route("/cache-clear", methods={"GET"}, name="admin_translation_cache_clear", options={"expose"=true})
     *
     * @param Request $request
     * @param Extractor $extractor
     * @return RedirectResponse|JsonResponse
     */
    public function cacheClear(Request $request, Extractor $extractor)
    {
        $extractor->clearCache();

        if ($request->get('ajax')) {
            return new JsonResponse(['success' => true]);
        }

        $request->getSession()->getFlashBag()->add('command', [
            'dirname' => $this->kernel->getCacheDir() . '/translations',
            'command' => 'Filesystem remove',
            'output' => $this->translator->trans('Le cache des traductions a été supprimé !!', [], 'admin')
        ]);

        return $this->redirect($request->headers->get('referer'));
    }
}