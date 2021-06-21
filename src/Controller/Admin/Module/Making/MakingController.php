<?php

namespace App\Controller\Admin\Module\Making;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Making\Making;
use App\Entity\Layout\BlockType;
use App\Form\Manager\Module\MakingManager;
use App\Form\Type\Module\Making\MakingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MakingController
 *
 * Making Action management
 *
 * @Route("/admin-%security_token%/{website}/makings", schemes={"%protocol%"})
 * @IsGranted("ROLE_MAKING")
 *
 * @property Making $class
 * @property MakingType $formType
 * @property MakingManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MakingController extends AdminController
{
    protected $class = Making::class;
    protected $formType = MakingType::class;
    protected $formManager = MakingManager::class;

    /**
     * Index Making
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_making_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Making
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_making_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Making
     *
     * @Route("/edit/{making}", methods={"GET", "POST"}, name="admin_making_edit")
     * @Route("/layout/{making}", methods={"GET", "POST"}, name="admin_making_layout")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $mediasCategoriesActive = $this->getWebsite($request)->getConfiguration()->getMediasCategoriesStatus();
        if (!$mediasCategoriesActive) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $this->translator->trans("Vous devez activer les catégories de médias dans la configration du site.", [], 'admin'));
        }

        $this->arguments['blockTypesDisabled'] = ['layout' => ['']];
        $this->arguments['blockTypesCategories'] = ['layout', 'content', 'global', 'action', 'modules'];
        $this->arguments['blockTypeAction'] = $this->entityManager->getRepository(BlockType::class)->findOneBySlug('core-action');

        return parent::edit($request);
    }

    /**
     * Position Making
     *
     * @Route("/position/{making}", methods={"GET", "POST"}, name="admin_making_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Making
     *
     * @Route("/delete/{making}", methods={"DELETE"}, name="admin_making_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}