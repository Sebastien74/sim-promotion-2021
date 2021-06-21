<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\Form;
use App\Form\Manager\Module\FormCalendarManager;
use App\Form\Type\Module\Form\CalendarType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * CalendarController
 *
 * Form Calendar Action management
 *
 * @Route("/admin-%security_token%/{website}/forms/calendars", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM_CALENDAR")
 *
 * @property Calendar $class
 * @property CalendarType $formType
 * @property FormCalendarManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarController extends AdminController
{
    const MULTIPLES_CALENDARS = true;

    protected $class = Calendar::class;
    protected $formType = CalendarType::class;
    protected $formManager = FormCalendarManager::class;

    /**
     * Index Calendar
     *
     * @Route("/{form}/index", methods={"GET", "POST"}, name="admin_formcalendar_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        /** @var Form $form */
        $form = $this->entityManager->getRepository(Form::class)->find($request->get('form'));

        if(!self::MULTIPLES_CALENDARS && $form->getCalendars()->count() > 0) {
            $this->disableFormNew = true;
        }

        return parent::index($request);
    }

    /**
     * New Calendar
     *
     * @Route("/{form}/new", methods={"GET", "POST"}, name="admin_formcalendar_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Calendar
     *
     * @Route("/{form}/edit/{formcalendar}", methods={"GET", "POST"}, name="admin_formcalendar_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        /** @var FormCalendarManager $manager */
        $manager = $this->subscriber->get($this->formManager);
        /** @var Calendar $calendar */
        $calendar = $this->entityManager->getRepository($this->class)->find($request->get('formcalendar'));

        $manager->setSchedules($calendar);

        return parent::edit($request);
    }

    /**
     * Show Calendar
     *
     * @Route("/{form}/show/{formcalendar}", methods={"GET"}, name="admin_formcalendar_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Calendar
     *
     * @Route("/position/{formcalendar}", methods={"GET", "POST"}, name="admin_formcalendar_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Calendar
     *
     * @Route("/delete/{formcalendar}", methods={"DELETE"}, name="admin_formcalendar_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}