<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\CalendarTimeRange;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * CalendarTimeRangeController
 *
 * Form Calendar Time Range Action management
 *
 * @Route("/admin-%security_token%/{website}/forms/time-ranges", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM_CALENDAR")
 *
 * @property CalendarTimeRange $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarTimeRangeController extends AdminController
{
    protected $class = CalendarTimeRange::class;

    /**
     * Delete CalendarTimeRange
     *
     * @Route("/delete/{formcalendartimerange}", methods={"DELETE"}, name="admin_formcalendartimerange_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}