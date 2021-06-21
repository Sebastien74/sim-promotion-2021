<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Seo\Redirection;
use App\Form\Manager\Seo\ImportRedirectionManager;
use App\Form\Manager\Seo\RedirectionManager;
use App\Form\Type\Seo\ImportRedirectionType;
use App\Form\Type\Seo\RedirectionType;
use App\Form\Type\Seo\WebsiteRedirectionType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RedirectionController
 *
 * SEO Redirection management
 *
 * @Route("/admin-%security_token%/{website}/seo/redirections", schemes={"%protocol%"})
 * @IsGranted("ROLE_SEO")
 *
 * @property Redirection $class
 * @property RedirectionType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RedirectionController extends AdminController
{
    protected $class = Redirection::class;
    protected $formType = RedirectionType::class;

    /**
     * Edit Redirection
     *
     * @Route("/edit", methods={"GET", "POST"}, name="admin_redirection_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->entity = $this->getWebsite($request);
        $this->class = Website::class;
        $this->formType = WebsiteRedirectionType::class;
        $this->formManager = RedirectionManager::class;
        $this->template = 'admin/page/seo/redirection.html.twig';

        return parent::edit($request);
    }

    /**
     * New Redirection
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_redirection_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * New Redirection
     *
     * @Route("/import", methods={"GET", "POST"}, name="admin_redirection_import")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function import(Request $request)
    {
        $form = $this->createForm(ImportRedirectionType::class);
        $form->handleRequest($request);
        $arguments['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->subscriber->get(ImportRedirectionManager::class)->execute($form, $this->getWebsite($request));
            return new JsonResponse(['success' => $response, 'flashBag' => !$response, 'html' => $this->renderView('admin/page/seo/import.html.twig', $arguments)]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(['html' => $this->renderView('admin/page/seo/import.html.twig', $arguments)]);
        }

        return $this->cache('admin/page/seo/import.html.twig', $arguments);
    }

    /**
     * Delete Redirection
     *
     * @Route("/delete/{redirection}", methods={"DELETE"}, name="admin_redirection_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}