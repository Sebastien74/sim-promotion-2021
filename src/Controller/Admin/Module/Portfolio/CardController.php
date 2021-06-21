<?php

namespace App\Controller\Admin\Module\Portfolio;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Portfolio\Card;
use App\Entity\Layout\BlockType;
use App\Form\Type\Module\Portfolio\CardType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CardController
 *
 * Portfolio Card Action management
 *
 * @Route("/admin-%security_token%/{website}/portfolios/cards", schemes={"%protocol%"})
 * @IsGranted("ROLE_PORTFOLIO")
 *
 * @property Card $class
 * @property CardType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CardController extends AdminController
{
    protected $class = Card::class;
    protected $formType = CardType::class;

    /**
     * Index Card
     *
     * @Route("/index", methods={"GET", "POST"}, name="admin_portfoliocard_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Card
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_portfoliocard_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Card
     *
     * @Route("/edit/{portfoliocard}", methods={"GET", "POST"}, name="admin_portfoliocard_edit")
     * @Route("/layout/{portfoliocard}", methods={"GET", "POST"}, name="admin_portfoliocard_layout")
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
     * Show Card
     *
     * @Route("/show/{portfoliocard}", methods={"GET"}, name="admin_portfoliocard_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Card
     *
     * @Route("/position/{portfoliocard}", methods={"GET", "POST"}, name="admin_portfoliocard_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Card
     *
     * @Route("/delete/{portfoliocard}", methods={"DELETE"}, name="admin_portfoliocard_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}