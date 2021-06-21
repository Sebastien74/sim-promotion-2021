<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Form\Manager\Api\GoogleManager;
use App\Form\Manager\Core\WebsiteManager;
use App\Form\Manager\Information\SocialNetworkManager;
use App\Form\Type\Core\Website\WebsiteType;
use App\Form\Type\Core\WebsitesSelectorType;
use App\Repository\Core\WebsiteRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * WebsiteController
 *
 * Website management
 *
 * @Route("/admin-%security_token%/{website}/websites", schemes={"%protocol%"})
 *
 * @property Website $class
 * @property WebsiteType $formType
 * @property WebsiteManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteController extends AdminController
{
    protected $class = Website::class;
    protected $formType = WebsiteType::class;
    protected $formManager = WebsiteManager::class;

    /**
     * Index Website
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_site_index")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Website
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_site_new")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Website
     *
     * @Route("/edit/{site}", methods={"GET", "POST"}, name="admin_site_edit")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $website = $this->getWebsite($request);

        $this->template = 'admin/page/website/website.html.twig';
        $this->subscriber->get(SocialNetworkManager::class)->synchronizeLocales($website, $website->getSeoConfiguration());
        $this->subscriber->get(GoogleManager::class)->synchronizeLocales($website, $website->getSeoConfiguration());

        return parent::edit($request);
    }

    /**
     * Show Website
     *
     * @Route("/show/{site}", methods={"GET"}, name="admin_site_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Show Website
     *
     * @Route("/selector", methods={"GET", "POST"}, name="admin_site_selector")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function websitesSelector(Request $request, WebsiteRepository $websiteRepository)
    {
        $websitesCount = count($websiteRepository->findAll());

        if($websitesCount === 1) { return new Response(); }

        /** @var User $user */
        $user = $this->getUser();
        $isInternalUser = $this->authorizationChecker->isGranted('ROLE_INTERNAL');
        $websites = $isInternalUser ? $websiteRepository->findAll() : $user->getWebsites();

        $form = $this->createForm(WebsitesSelectorType::class, NULL, [
            'website' => $this->getWebsite($request)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = (object) $form->getData();
            if(intval($data->websites) === 2) {
                return $this->redirectToRoute('admin_newscast_index', ['website' => $data->websites]);
            }
            return $this->redirectToRoute('admin_page_tree', ['website' => $data->websites]);
        }

        return $this->cache('admin/include/sidebar/include/websites-selector.html.twig', [
            'websites' => $websites,
            'form' => $form->createView()
        ]);
    }
}