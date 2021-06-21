<?php

namespace App\Controller\Admin\Module\Table;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Table\Cell;
use App\Entity\Module\Table\Table;
use App\Entity\Core\Website;
use App\Form\Manager\Module\TableManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CellController
 *
 * Table Cell Action management
 *
 * @Route("/admin-%security_token%/{website}/tables/cells", schemes={"%protocol%"})
 * @IsGranted("ROLE_TABLE")
 *
 * @property Cell $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CellController extends AdminController
{
    protected $class = Cell::class;

    /**
     * Add Cell
     *
     * @Route("/{table}/add", methods={"GET"}, name="admin_tablecell_add")
     *
     * {@inheritdoc}
     */
    public function addRow(Request $request, Website $website, Table $table)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->addRow($table, $website);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Position Cell
     *
     * @Route("/position/{table}/{position}/{type}", methods={"GET", "POST"}, name="admin_tablecell_position")
     *
     * @param Request $request
     * @param Table $table
     * @param int $position
     * @param string $type
     * @return RedirectResponse
     */
    public function rowPosition(Request $request, Table $table, int $position, string $type)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->rowPosition($table, $position, $type);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Delete Cell
     *
     * @Route("/delete/{table}/{position}", methods={"DELETE", "GET"}, name="admin_tablecell_delete")
     *
     * @param Table $table
     * @param int $position
     * @return JsonResponse
     */
    public function deleteRow(Table $table, int $position)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->deleteRow($table, $position);
        return new JsonResponse(['success' => true, 'reload' => true]);
    }
}