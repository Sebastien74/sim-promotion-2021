<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\CalendarAppointment;
use App\Form\Type\Module\Form\CalendarAppointmentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * CalendarAppointmentController
 *
 * Form Calendar Action management
 *
 * @Route("/admin-%security_token%/{website}/forms/calendars/appointments", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM_CALENDAR")
 *
 * @property CalendarAppointment $class
 * @property CalendarAppointmentType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointmentController extends AdminController
{
    protected $class = CalendarAppointment::class;
    protected $formType = CalendarAppointmentType::class;

    /**
     * Index CalendarAppointment
     *
     * @Route("/{formcalendar}/index", methods={"GET", "POST"}, name="admin_formcalendarappointment_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Edit CalendarAppointment
     *
     * @Route("/{formcalendar}/edit/{formcalendarappointment}", methods={"GET", "POST"}, name="admin_formcalendarappointment_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show CalendarAppointment
     *
     * @Route("/{formcalendar}/show/{formcalendarappointment}", methods={"GET"}, name="admin_formcalendarappointment_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Export CalendarAppointment[]
     *
     * @Route("/export", methods={"GET", "POST"}, name="admin_formcalendarappointment_export")
     *
     * {@inheritdoc}
     */
    public function export(Request $request)
    {
        return parent::export($request);
    }

    /**
     * Delete CalendarAppointment
     *
     * @Route("/delete/{formcalendarappointment}", methods={"DELETE"}, name="admin_formcalendarappointment_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}