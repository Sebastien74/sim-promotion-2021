<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use App\Form\Manager\Core\EntityConfigurationManager;
use App\Form\Type\Core\EntityType;
use App\Service\Development\EntityService;
use App\Service\Translation\Extractor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EntityController
 *
 * Entity management
 *
 * @Route("/admin-%security_token%/{website}/configuration/entities", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Entity $class
 * @property EntityType $formType
 * @property EntityConfigurationManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityController extends AdminController
{
    protected $class = Entity::class;
    protected $formType = EntityType::class;
    protected $formManager = EntityConfigurationManager::class;

    /**
     * Entities generator
     *
     * @Route("/generate", methods={"GET"}, name="admin_entities_generator")
     * @IsGranted("ROLE_INTERNAL")
     *
     * @param Request $request
     * @param Website $website
     * @param EntityService $entityService
     * @param Extractor $extractor
     * @return RedirectResponse
     */
    public function generate(Request $request, Website $website, EntityService $entityService, Extractor $extractor): RedirectResponse
    {
        foreach ($website->getConfiguration()->getAllLocales() as $locale) {
            $entityService->execute($website, $locale);
        }

        $configuration = $website->getConfiguration();
        $extractor->extractEntities($website, $configuration->getLocale(), $configuration->getAllLocales());
        $extractor->clearCache();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Index Entity
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_entity_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Entity
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_entity_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request): Response
    {
        return parent::new($request);
    }

    /**
     * Edit Entity
     *
     * @Route("/edit/{entity}", methods={"GET", "POST"}, name="admin_entity_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request): Response
    {
        $this->template = 'admin/page/core/entity.html.twig';
        return parent::edit($request);
    }

    /**
     * Show Entity
     *
     * @Route("/show/{entity}", methods={"GET"}, name="admin_entity_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Entity
     *
     * @Route("/position/{entity}", methods={"GET", "POST"}, name="admin_entity_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Entity
     *
     * @Route("/delete/{entity}", methods={"DELETE"}, name="admin_entity_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}