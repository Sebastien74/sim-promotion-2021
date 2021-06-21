<?php

namespace App\Controller\Admin\Translation;

use App\Controller\Admin\AdminController;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationUnit;
use App\Form\Manager\Translation\UnitManager;
use App\Form\Type\Translation\UnitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * UnitController
 *
 * Translation Unit management
 *
 * @Route("/admin-%security_token%/{website}/translations/units", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property TranslationUnit $class
 * @property UnitType $formType
 * @property UnitManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UnitController extends AdminController
{
    protected $class = TranslationUnit::class;
    protected $formType = UnitType::class;
    protected $formManager = UnitManager::class;

    /**
     * Edit TranslationUnit
     *
     * @Route("/edit/{translationunit}/{displayDomain}", defaults={"displayDomain": null}, methods={"GET", "POST"}, name="admin_translationunit_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->entity = $this->entityManager->getRepository(TranslationUnit::class)->find($request->get('translationunit'));
        if (!$this->entity) {
            throw $this->createNotFoundException($this->translator->trans("Cette clé n'existe pas !!", [], 'admin'));
        }

        $this->disableProfiler();
        $this->template = 'admin/page/translation/unit.html.twig';
        $this->arguments['displayDomain'] = $request->get('displayDomain');

        return parent::edit($request);
    }

    /**
     * Regenerate TranslationUnit
     *
     * @Route("/regenerate/{translationUnit}", methods={"GET"}, name="admin_translationunit_regenerate")
     *
     * @param Request $request
     * @param TranslationUnit $translationUnit
     * @return RedirectResponse
     */
    public function regenerate(Request $request, TranslationUnit $translationUnit)
    {
        $website = $this->getWebsite($request);

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {

            $translation = $this->entityManager->getRepository(Translation::class)->findOneBy([
                'unit' => $translationUnit,
                'locale' => $locale
            ]);

            if (!$translation) {

                $translation = new Translation();
                $translation->setLocale($locale);
                $translation->setUnit($translationUnit);

                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Delete TranslationUnit
     *
     * @Route("/delete/{translationunit}", methods={"DELETE"}, name="admin_translationunit_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        /** @var TranslationUnit $unit */
        $unit = $this->entityManager->getRepository(TranslationUnit::class)->find($request->get('translationunit'));

        if (!$unit) {
            return new JsonResponse(['success' => false]);
        }

        $website = $this->getWebsite($request);
        $domainName = $unit->getDomain()->getName();

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {

            $finder = new Finder();
            $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/translations/');
            $finder->files()->in($this->kernel->getProjectDir() . $dirname)->name($domainName . '+intl-icu.' . $locale . '.yaml');

            foreach ($finder as $file) {
                $values = Yaml::parseFile($file->getPathname());
                if (is_array($values) && !empty($values[$unit->getKeyname()])) {
                    unset($values[$unit->getKeyname()]);
                    $yaml = Yaml::dump($values);
                    file_put_contents($file->getPathname(), $yaml);
                }
            }
        }

        return parent::delete($request);
    }
}