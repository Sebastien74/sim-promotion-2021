<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Catalog;
use App\Form\Manager\Module\CatalogManager;
use App\Form\Type\Module\Catalog\CatalogType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CatalogController
 *
 * Catalog management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/catalogs", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Catalog $class
 * @property CatalogType $formType
 * @property CatalogManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogController extends AdminController
{
    protected $class = Catalog::class;
    protected $formType = CatalogType::class;
    protected $formManager = CatalogManager::class;

    /**
     * Index Catalog
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_catalog_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Catalog
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_catalog_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Catalog
     *
     * @Route("/edit/{catalog}", methods={"GET", "POST"}, name="admin_catalog_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Catalog
     *
     * @Route("/show/{catalog}", methods={"GET"}, name="admin_catalog_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Catalog
     *
     * @Route("/position/{catalog}", methods={"GET", "POST"}, name="admin_catalog_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Catalog
     *
     * @Route("/delete/{catalog}", methods={"DELETE"}, name="admin_catalog_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}