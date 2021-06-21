<?php

namespace App\Controller\Admin\Module\Search;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Search\Search;
use App\Form\Type\Module\Search\SearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SearchController
 *
 * Search Action management
 *
 * @Route("/admin-%security_token%/{website}/searchs", schemes={"%protocol%"})
 * @IsGranted("ROLE_SEARCH_ENGINE")
 *
 * @property Search $class
 * @property SearchType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchController extends AdminController
{
    protected $class = Search::class;
    protected $formType = SearchType::class;

    /**
     * Index Search
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_search_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Search
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_search_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Search
     *
     * @Route("/edit/{search}", methods={"GET", "POST"}, name="admin_search_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Search
     *
     * @Route("/show/{search}", methods={"GET"}, name="admin_search_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Search
     *
     * @Route("/position/{search}", methods={"GET", "POST"}, name="admin_search_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Search
     *
     * @Route("/delete/{search}", methods={"DELETE"}, name="admin_search_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}