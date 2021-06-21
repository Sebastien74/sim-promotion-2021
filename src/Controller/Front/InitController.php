<?php

namespace App\Controller\Front;

use App\Form\Manager\Core\InitManager;
use App\Form\Type\Core\Init as Type;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * InitController
 *
 * Manage new project
 *
 * @Route("/{_locale}/sfcms/project", schemes={"%protocol%"})
 *
 * @property array IPS_DEV
 *
 * @property array $steps
 * @property array $cmdSteps
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InitController extends FrontController
{
    private $steps = [];
    private $cmdSteps = [];

    /**
     * Project initialization view
     *
     * @Route("/initialization/{step}", methods={"GET", "POST"}, name="app_new_website_project")
     *
     * @param Request $request
     * @param InitManager $initManager
     * @param string $step
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function initialization(Request $request, InitManager $initManager, string $step)
    {
        $this->setStepArguments($request);

        $newStep = NULL;
        $stepArguments = !empty($this->steps[$step]) ? $this->steps[$step] : (!empty($this->cmdSteps[$step]) ? $this->cmdSteps[$step] : []);
        $stepPosition = !empty($stepArguments['position']) ? $stepArguments['position'] : NULL;
        $form = NULL;

        if (!empty($stepArguments['formType'])) {
            $form = $this->createForm($stepArguments['formType'], NULL);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $newStep = $initManager->execute($form, $this->steps, $stepPosition, $step);
                if ($newStep) {
                    return $this->redirectToRoute('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => $newStep]);
                }
            }
        }
        elseif (!empty($stepArguments['run'])) {
            $method = $stepArguments['run'];
            $initManager->$method();
            return new JsonResponse(['success' => true, 'redirect' => $stepArguments['redirect']]);
        }
        elseif ($step === 'success') {
            $initManager->disableServices();
            $stepArguments = [
                'size' => 7,
                'steps' => NULL,
                'title' => $this->translator->trans("Bravo !!", [], 'core_init'),
            ];
        }

        return $this->render('core/page/init/view.html.twig', array_merge([
            'form' => $form ? $form->createView() : NULL,
            'steps' => $this->steps,
            'stepPosition' => $stepPosition,
            'step' => $step,
            'newStep' => $newStep
        ], $stepArguments));
    }

    /**
     * Get step arguments
     *
     * @param Request $request
     */
    private function setStepArguments(Request $request)
    {
        $this->steps['configuration'] = [
            'formType' => Type\ConfigurationType::class,
            'position' => 1,
            'size' => 8,
            'title' => $this->translator->trans("Configuration du site", [], 'core_init'),
        ];
        $this->steps['internationalization'] = [
            'formType' => Type\InternationalizationType::class,
            'position' => 2,
            'title' => $this->translator->trans("Configuration des langues", [], 'core_init'),
        ];
        $this->steps['contact'] = [
            'formType' => Type\ContactType::class,
            'position' => 3,
            'size' => 8,
            'title' => $this->translator->trans("Coordonnées", [], 'core_init'),
        ];
        $this->steps['legals'] = [
            'formType' => Type\LegalsType::class,
            'position' => 4,
            'title' => $this->translator->trans("Informations légales", [], 'core_init'),
        ];
        $this->steps['networks'] = [
            'formType' => Type\NetworksType::class,
            'position' => 5,
            'title' => $this->translator->trans("Réseaux sociaux", [], 'core_init'),
        ];
        $this->steps['styles'] = [
            'formType' => Type\StylesType::class,
            'position' => 6,
            'title' => $this->translator->trans("Styles", [], 'core_init'),
        ];
        $this->steps['data-base'] = [
            'formType' => Type\DataBaseType::class,
            'position' => 7,
            'size' => 4,
            'nextStep' => 'generate-db',
            'title' => $this->translator->trans("Base de données", [], 'core_init'),
        ];
        $this->steps['generate-yarn'] = [
            'position' => 8,
            'size' => 8,
            'newStep' => 'yarn-install',
            'title' => $this->translator->trans("Yarn", [], 'core_init'),
            'subTitle' => $this->translator->trans("En cours d'installation", [], 'core_init'),
        ];
        $this->steps['generate-assets'] = [
            'position' => 9,
            'size' => 8,
            'newStep' => 'encore-install',
            'title' => $this->translator->trans("Assets", [], 'core_init'),
            'subTitle' => $this->translator->trans("En cours de génération", [], 'core_init'),
        ];

        $this->cmdSteps['generate-db'] = [
            'size' => 8,
            'position' => 7,
            'title' => $this->translator->trans("Base de données", [], 'core_init'),
            'subTitle' => $this->translator->trans("Base de données en cours de création", [], 'core_init'),
            'newStep' => 'generate-db-create',
        ];
        $this->cmdSteps['generate-db-create'] = [
            'run' => 'generateDBCreate',
            'redirect' => $this->generateUrl('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => 'generate-db-data'])
        ];
        $this->cmdSteps['generate-db-data'] = [
            'size' => 8,
            'position' => 7,
            'title' => $this->translator->trans("Base de données", [], 'core_init'),
            'subTitle' => $this->translator->trans("Insertion des données", [], 'core_init'),
            'newStep' => 'generate-db-fixtures',
        ];
        $this->cmdSteps['generate-db-fixtures'] = [
            'run' => 'generateDBFixtures',
            'redirect' => $this->generateUrl('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => 'generate-db-internationalization'])
        ];
        $this->cmdSteps['generate-db-internationalization'] = [
            'size' => 8,
            'position' => 7,
            'title' => $this->translator->trans("Base de données", [], 'core_init'),
            'subTitle' => $this->translator->trans("Insertion des traductions", [], 'core_init'),
            'newStep' => 'generate-db-translations',
        ];
        $this->cmdSteps['generate-db-translations'] = [
            'run' => 'generateDBTranslations',
            'redirect' => $this->generateUrl('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => 'generate-yarn'])
        ];
        $this->cmdSteps['yarn-install'] = [
            'run' => 'yarnInstall',
            'redirect' => $this->generateUrl('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => 'generate-assets'])
        ];
        $this->cmdSteps['encore-install'] = [
            'run' => 'encoreInstall',
            'redirect' => $this->generateUrl('app_new_website_project', ['_locale' => $request->getLocale(), 'step' => 'success'])
        ];
    }
}