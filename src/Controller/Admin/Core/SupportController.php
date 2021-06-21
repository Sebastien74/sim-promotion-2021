<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Form\Manager\Core\SupportManager;
use App\Form\Type\Core\SupportType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SupportController
 *
 * Send email to support
 *
 * @Route("/admin-%security_token%/{website}/support", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SupportController extends AdminController
{
    /**
     * Contact
     *
     * @Route("/contact", methods={"GET", "POST"}, name="admin_support")
     *
     * @param Request $request
     * @param Website $website
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function contact(Request $request, Website $website)
    {
        $form = $this->createForm(SupportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->subscriber->get(SupportManager::class)->send($form->getData(), $website);
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->cache('admin/page/core/support.html.twig', [
            'form' => $form->createView()
        ]);
    }
}