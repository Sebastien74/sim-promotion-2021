<?php

namespace App\Controller\Admin\Module\Table;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Table\Table;
use App\Form\Manager\Module\TableManager;
use App\Form\Type\Module\Table\TableType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TableController
 *
 * Table Action management
 *
 * @Route("/admin-%security_token%/{website}/tables", schemes={"%protocol%"})
 * @IsGranted("ROLE_TABLE")
 *
 * @property Table $class
 * @property TableType $formType
 * @property TableManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableController extends AdminController
{
    protected $class = Table::class;
    protected $formType = TableType::class;
    protected $formManager = TableManager::class;

    /**
     * Index Table
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_table_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Table
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_table_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Table
     *
     * @Route("/edit/{table}/{entitylocale}", methods={"GET", "POST"}, name="admin_table_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->disableProfiler();
        $this->template = 'admin/page/table/edit.html.twig';
        return parent::edit($request);
    }

    /**
     * Show Table
     *
     * @Route("/show/{table}/{entitylocale}", methods={"GET"}, name="admin_table_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Table
     *
     * @Route("/position/{table}", methods={"GET", "POST"}, name="admin_table_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Table
     *
     * @Route("/delete/{table}", methods={"DELETE"}, name="admin_table_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}