<?php

namespace App\Controller\Admin\Module\Event;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Event\Event;
use App\Entity\Layout\BlockType;
use App\Form\Manager\Module\EventManager;
use App\Form\Type\Module\Event\EventType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EventController
 *
 * Event Action management
 *
 * @Route("/admin-%security_token%/{website}/events", schemes={"%protocol%"})
 * @IsGranted("ROLE_EVENT")
 *
 * @property Event $class
 * @property EventType $formType
 * @property EventManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EventController extends AdminController
{
    protected $class = Event::class;
    protected $formType = EventType::class;
    protected $formManager = EventManager::class;

    /**
     * Index Event
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_event_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Event
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_event_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Event
     *
     * @Route("/edit/{event}", methods={"GET", "POST"}, name="admin_event_edit")
     * @Route("/layout/{event}", methods={"GET", "POST"}, name="admin_event_layout")
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
     * Position Event
     *
     * @Route("/position/{event}", methods={"GET", "POST"}, name="admin_event_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Export Event[]
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_event_export")
     *
     * {@inheritdoc}
     */
    public function export(Request $request)
    {
        return parent::export($request);
    }

    /**
     * Delete Event
     *
     * @Route("/delete/{event}", methods={"DELETE"}, name="admin_event_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}