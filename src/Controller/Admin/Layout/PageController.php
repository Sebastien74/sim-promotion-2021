<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\Page;
use App\Form\Manager\Layout\PageDuplicateManager;
use App\Form\Manager\Layout\PageManager;
use App\Form\Type\Layout\PageDuplicateType;
use App\Form\Type\Layout\PageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PageController
 *
 * Page management
 *
 * @Route("/admin-%security_token%/{website}/pages", schemes={"%protocol%"})
 * @IsGranted("ROLE_PAGE")
 *
 * @property Page $class
 * @property PageType $formType
 * @property PageManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageController extends AdminController
{
    protected $class = Page::class;
    protected $formType = PageType::class;
    protected $formManager = PageManager::class;

    /**
     * Pages tree
     *
     * @Route("/tree", methods={"GET", "POST"}, name="admin_page_tree")
     *
     * {@inheritdoc}
     */
    public function tree(Request $request)
    {
        return parent::tree($request);
    }

    /**
     * New Page
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_page_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Layout Page
     *
     * @Route("/layout/{page}", methods={"GET", "POST"}, name="admin_page_layout")
     *
     * {@inheritdoc}
     */
    public function layout(Request $request)
    {
        $this->templateConfig = 'admin/page/content/page-configuration.html.twig';
        return parent::layout($request);
    }

    /**
     * Duplicate Page
     *
     * @Route("/duplicate/{page}", methods={"GET", "POST"}, name="admin_page_duplicate")
     *
     * {@inheritdoc}
     */
    public function duplicate(Request $request)
    {
        $this->formType = PageDuplicateType::class;
        $this->formDuplicateManager = PageDuplicateManager::class;
        return parent::duplicate($request);
    }

    /**
     * Delete Page
     *
     * @Route("/delete/{page}", methods={"DELETE"}, name="admin_page_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}