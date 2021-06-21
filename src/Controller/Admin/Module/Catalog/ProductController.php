<?php

namespace App\Controller\Admin\Module\Catalog;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Catalog\Product;
use App\Form\Manager\Module\CatalogProductManager;
use App\Form\Type\Module\Catalog\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ProductController
 *
 * Catalog Product management
 *
 * @Route("/admin-%security_token%/{website}/module/catalogs/products", schemes={"%protocol%"})
 * @IsGranted("ROLE_CATALOG")
 *
 * @property Product $class
 * @property ProductType $formType
 * @property CatalogProductManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProductController extends AdminController
{
    protected $class = Product::class;
    protected $formType = ProductType::class;
    protected $formManager = CatalogProductManager::class;

    /**
     * Index Product
     *
     * @Route("/index/{catalog}", defaults={"catalog": null}, methods={"GET", "POST"}, name="admin_catalogproduct_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Product
     *
     * @Route("/new/{catalog}", defaults={"catalog": null}, methods={"GET", "POST"}, name="admin_catalogproduct_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Product
     *
     * @Route("/edit/{catalogproduct}/{catalog}/{tab}", defaults={"catalog": null, "tab": null}, methods={"GET", "POST"}, name="admin_catalogproduct_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->template = 'admin/page/catalog/product-edit.html.twig';
        $this->arguments['activeTab'] = $request->get('tab');

        return parent::edit($request);
    }

    /**
     * Medias Product
     *
     * @Route("/medias/{catalogproduct}/{catalog}", defaults={"catalog": null}, methods={"GET", "POST"}, name="admin_catalogproduct_medias")
     *
     * @param Request $request
     * @return Response
     */
    public function medias(Request $request)
    {
        return $this->cache('admin/page/catalog/product-medias.html.twig', [
            'entity' => $this->entityManager->getRepository(Product::class)->find($request->get('catalogproduct')),
            'website' => $this->getWebsite($request),
            'interface' => $this->getInterface($this->class),
        ]);
    }

    /**
     * Show Product
     *
     * @Route("/show/{catalogproduct}/{catalog}", defaults={"catalog": null}, methods={"GET"}, name="admin_catalogproduct_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Product
     *
     * @Route("/position/{catalogproduct}/{catalog}", defaults={"catalog": null}, methods={"GET", "POST"}, name="admin_catalogproduct_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Product
     *
     * @Route("/delete/{catalogproduct}", methods={"DELETE"}, name="admin_catalogproduct_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}