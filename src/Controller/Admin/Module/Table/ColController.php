<?php

namespace App\Controller\Admin\Module\Table;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Table\Col;
use App\Entity\Module\Table\Table;
use App\Entity\Core\Website;
use App\Form\Manager\Module\TableManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ColController
 *
 * Table Col Action management
 *
 * @Route("/admin-%security_token%/{website}/tables/cols", schemes={"%protocol%"})
 * @IsGranted("ROLE_TABLE")
 *
 * @property Col $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColController extends AdminController
{
    protected $class = Col::class;

    /**
     * Add Col
     *
     * @Route("/{table}/add", methods={"GET"}, name="admin_tablecol_add")
     *
     * {@inheritdoc}
     */
    public function add(Request $request, Website $website, Table $table)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->addCol($table, $website);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Position Col
     *
     * @Route("/{table}/position/{col}/{type}", methods={"GET", "POST"}, name="admin_tablecol_position")
     *
     * @param Request $request
     * @param Table $table
     * @param Col $col
     * @param string $type
     * @return RedirectResponse
     */
    public function colPosition(Request $request, Table $table, Col $col, string $type)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->colPosition($table, $col, $type);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Delete Col
     *
     * @Route("/{table}/delete/{col}", methods={"DELETE"}, name="admin_tablecol_delete")
     *
     * @param Request $request
     * @param Table $table
     * @param Col $col
     * @return RedirectResponse
     */
    public function deleteCol(Request $request, Table $table, Col $col)
    {
        $manager = $this->subscriber->get(TableManager::class);
        $manager->deleteCol($table, $col);
        return new JsonResponse(['success' => true, 'reload' => true]);
    }
}