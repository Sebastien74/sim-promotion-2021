<?php

namespace App\Controller\Admin\Module\Form;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Form\CalendarException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * CalendarExceptionController
 *
 * Form Calendar Exception management
 *
 * @Route("/admin-%security_token%/{website}/forms/exceptions", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM_CALENDAR")
 *
 * @property CalendarException $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarExceptionController extends AdminController
{
    protected $class = CalendarException::class;

    /**
     * Delete CalendarException
     *
     * @Route("/delete/{formcalendarexception}", methods={"DELETE"}, name="admin_formcalendarexception_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}