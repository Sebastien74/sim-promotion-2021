<?php

namespace App\Controller\Admin\Module\Newscast;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Layout\BlockType;
use App\Form\Manager\Module\NewscastManager;
use App\Form\Type\Module\Newscast\NewscastType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NewscastController
 *
 * Newscast Action management
 *
 * @Route("/admin-%security_token%/{website}/newscasts", schemes={"%protocol%"})
 * @IsGranted("ROLE_NEWSCAST")
 *
 * @property Newscast $class
 * @property NewscastType $formType
 * @property NewscastManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastController extends AdminController
{
    protected $class = Newscast::class;
    protected $formType = NewscastType::class;
    protected $formManager = NewscastManager::class;

    /**
     * Index Newscast
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_newscast_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Newscast
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_newscast_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Newscast
     *
     * @Route("/edit/{newscast}", methods={"GET", "POST"}, name="admin_newscast_edit")
     * @Route("/layout/{newscast}", methods={"GET", "POST"}, name="admin_newscast_layout")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->arguments['blockTypesDisabled'] = ['layout' => ['']];
        $this->arguments['blockTypesCategories'] = ['layout', 'content', 'global', 'action', 'modules'];
        $this->arguments['blockTypeAction'] = $this->entityManager->getRepository(BlockType::class)->findOneBySlug('core-action');

        return parent::edit($request);
    }

    /**
     * Position Newscast
     *
     * @Route("/position/{newscast}", methods={"GET", "POST"}, name="admin_newscast_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Export Newscast[]
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_newscast_export")
     *
     * {@inheritdoc}
     */
    public function export(Request $request)
    {
        return parent::export($request);
    }

    /**
     * Delete Newscast
     *
     * @Route("/delete/{newscast}", methods={"DELETE"}, name="admin_newscast_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}