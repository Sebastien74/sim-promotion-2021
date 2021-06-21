<?php

namespace App\Controller\Admin\Module\Menu;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Menu\Menu;
use App\Entity\Module\Menu\Link;
use App\Form\Manager\Module\AddLinkManager;
use App\Form\Type\Module\Menu\LinkType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LinkController
 *
 * Link Menu Action management
 *
 * @Route("/admin-%security_token%/{website}/menus/links", schemes={"%protocol%"})
 * @IsGranted("ROLE_NAVIGATION")
 *
 * @property Link $class
 * @property LinkType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LinkController extends AdminController
{
    protected $class = Link::class;
    protected $formType = LinkType::class;

    /**
     * Add Links
     *
     * @Route("/add/{menu}/{locale}/{multiple}", methods={"GET", "POST"}, name="admin_link_add")
     *
     * @param Request $request
     * @param Menu $menu
     * @param string $locale
     * @param bool $multiple
     * @return RedirectResponse
     */
    public function add(Request $request, Menu $menu, string $locale, bool $multiple)
    {
        $post = filter_input_array(INPUT_POST);
        if ($post) {
            $formManager = $this->subscriber->get(AddLinkManager::class);
            $formManager->post($post, $menu, $locale, $multiple);
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * New Link
     *
     * @Route("/new/{menu}/{entitylocale}", methods={"GET", "POST"}, name="admin_link_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        $this->template = 'admin/page/menu/new-link.html.twig';
        $this->arguments['menu'] = $request->get('menu');
        return parent::new($request);
    }

    /**
     * Edit Link
     *
     * @Route("/edit/{link}/{entitylocale}", name="admin_link_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $link = $this->entityManager->getRepository(Link::class)->find($request->get('link'));
        if (!$link) {
            throw $this->createNotFoundException($this->translator->trans("Ce lien n'existe pas !!", [], 'front'));
        }

        if ($link->getLocale() !== $request->get('entitylocale')) {
            throw $this->createNotFoundException();
        }

        return parent::edit($request);
    }

    /**
     * Delete Link
     *
     * @Route("/delete/{link}", methods={"DELETE"}, name="admin_link_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}