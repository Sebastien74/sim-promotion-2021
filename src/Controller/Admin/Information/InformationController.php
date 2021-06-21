<?php

namespace App\Controller\Admin\Information;

use App\Controller\Admin\AdminController;
use App\Entity\Information\Information;
use App\Form\Manager\Information\InformationManager;
use App\Form\Type\Information\InformationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * InformationController
 *
 * Information management
 *
 * @Route("/admin-%security_token%/{website}/information", schemes={"%protocol%"})
 * @IsGranted("ROLE_INFORMATION")
 *
 * @property Information $class
 * @property InformationType $formType
 * @property InformationManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationController extends AdminController
{
    protected $class = Information::class;
    protected $formType = InformationType::class;
    protected $formManager = InformationManager::class;

    /**
     * Edit Information
     *
     * @Route("/edit/{information}/{tab}", defaults={"tab": null}, methods={"GET", "POST"}, name="admin_information_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->template = 'admin/page/information/information.html.twig';
        return parent::edit($request);
    }
}