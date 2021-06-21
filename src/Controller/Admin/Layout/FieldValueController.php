<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Layout\FieldValue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FieldValueController
 *
 * Layout FieldValue management
 *
 * @Route("/admin-%security_token%/{website}/layouts/fields/values", schemes={"%protocol%"})
 * @IsGranted("ROLE_FORM")
 *
 * @property FieldValue $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldValueController extends AdminController
{
    protected $class = FieldValue::class;

    /**
     * Delete FieldValue
     *
     * @Route("/delete/{fieldvalue}", methods={"DELETE"}, name="admin_fieldvalue_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        /** @var FieldValue $fieldValue */
        $fieldValue = $this->entityManager->getRepository($this->class)->find($request->get('fieldvalue'));
        $this->entities = $fieldValue ? $fieldValue->getConfiguration()->getFieldValues() : [];

        return parent::delete($request);
    }

    /**
     * Position FieldValue
     *
     * @Route("/position/{fieldvalue}", methods={"GET"}, name="admin_fieldvalue_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }
}