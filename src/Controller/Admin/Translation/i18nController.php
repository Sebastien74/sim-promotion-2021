<?php

namespace App\Controller\Admin\Translation;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Form\Type\Translation\ImportType;
use App\Service\Translation\ExportService;
use App\Service\Translation\ImportService;
use Doctrine\ORM\Mapping\MappingException;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * i18nController
 *
 * Translation management
 *
 * @Route("/admin-%security_token%/{website}/translations/i18ns", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nController extends AdminController
{
    /**
     * Export translations
     *
     * @Route("/export", methods={"GET"}, name="admin_i18ns_export")
     *
     * @param Request $request
     * @param Website $website
     * @param ExportService $exportService
     * @return Response
     * @throws Exception
     * @throws MappingException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function exportation(Request $request, Website $website, ExportService $exportService)
    {
        $exportService->execute($website);
        $zipName = $exportService->zip();

        if (!$zipName) {
            $session = new Session();
            $session->getFlashBag()->add('info', $this->translator->trans("Vous n'avez aucun contenu à traduire.", [], 'admin'));
            return $this->redirect($request->headers->get('referer'));
        }

        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', filesize($zipName));

        @unlink($zipName);

        return $response;
    }

    /**
     * Import translations
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_i18ns_import")
     *
     * @param Request $request
     * @param ImportService $importService
     * @param Website $website
     * @return RedirectResponse|Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import(Request $request, ImportService $importService, Website $website)
    {
        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !empty($form->getData()['files'])) {
            $importService->execute($form->getData()['files']);
            $session = new Session();
            $session->getFlashBag()->add('success', $this->translator->trans("Importation réussie.", [], 'admin'));
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('admin/page/translation/import.html.twig', [
            'form' => $form->createView()
        ]);
    }
}