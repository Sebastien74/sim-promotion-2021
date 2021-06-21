<?php

namespace App\Controller\Admin\Layout;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Website;
use App\Entity\Layout\Action;
use App\Entity\Layout\Block;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\Col;
use App\Entity\Layout\FieldConfiguration;
use App\Entity\Layout\FieldValue;
use App\Entity\Layout\LayoutConfiguration;
use App\Entity\Translation\i18n;
use App\Form\Manager\Layout\BlockManager;
use App\Form\Type\Layout\Block as FormType;
use App\Form\Type\Layout\Management\BackgroundColorBlockType;
use App\Form\Type\Layout\Management\BlockConfigurationType;
use App\Repository\Layout\BlockRepository;
use App\Repository\Layout\ColRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BlockController
 *
 * Layout Block management
 *
 * @Route("/admin-%security_token%/{website}/layouts/zones/cols/blocks", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @property array FORM_TYPES
 * @property array FORM_TYPES_GROUPS
 *
 * @property Block $class
 * @property BlockManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockController extends AdminController
{
    private const FORM_TYPES = [
        'core-action' => FormType\ActionType::class,
        'alert' => FormType\AlertType::class,
        'blockquote' => FormType\BlockquoteType::class,
        'card' => FormType\CardType::class,
        'collapse' => FormType\CollapseType::class,
        'counter' => FormType\CounterType::class,
        'icon' => FormType\IconType::class,
        'link' => FormType\LinkType::class,
        'media' => FormType\MediaType::class,
        'modal' => FormType\ModalType::class,
        'separator' => FormType\SeparatorType::class,
        'text' => FormType\TextType::class,
        'titleheader' => FormType\TitleHeaderType::class,
        'title' => FormType\TitleType::class,
        'video' => FormType\VideoType::class,
        'widget' => FormType\WidgetType::class,
    ];

    private const FORM_TYPES_GROUPS = [
        'form' => [
            'formType' => FormType\FieldType::class,
            'template' => ''
        ]
    ];

    protected $class = Block::class;
    protected $formManager = BlockManager::class;

    /**
     * Delete Block
     *
     * @Route("/{interfaceName}/{interfaceEntity}/add/{col}/{blockType}/{action}", defaults={"action": null}, methods={"GET", "POST"}, name="admin_block_add")
     *
     * @param Request $request
     * @param string $interfaceName
     * @param int $interfaceEntity
     * @param Col $col
     * @param BlockType $blockType
     * @param Action|null $action
     * @return RedirectResponse
     */
    public function add(Request $request, string $interfaceName, int $interfaceEntity, Col $col, BlockType $blockType, Action $action = NULL)
    {
        $website = $this->getWebsite($request);
        $isLayout = preg_match('/layout/', $blockType->getSlug());
        $isForm = preg_match('/form/', $blockType->getSlug());
        $block = new Block();
        $block->setPosition(count($col->getBlocks()) + 1)
            ->setAction($action)
            ->setBlockType($blockType);

        if (preg_match('/form-/', $block->getBlockType()->getSlug())) {

            $fieldConfiguration = new FieldConfiguration();
            $fieldConfiguration->setBlock($block);
            $block->setFieldConfiguration($fieldConfiguration);

            if ($block->getBlockType()->getSlug() === 'form-gdpr') {
                $this->addGdprField($website, $fieldConfiguration);
            }
        }

        if (preg_match('/form-/', $blockType->getSlug())) {
            $block->setAdminName(str_replace(' (form)', '', $blockType->getAdminName()));
        }

        $col->addBlock($block);

        $this->entityManager->persist($col);
        $this->entityManager->flush();

        if (!$isLayout && !$block->getAction() && isset(self::FORM_TYPES[$block->getBlockType()->getSlug()]) || $isForm || !$isLayout && $block->getAction() && $action->getEntity()) {
            return $this->redirectToRoute('admin_block_edit', [
                'website' => $request->get('website'),
                'interfaceName' => $interfaceName,
                'interfaceEntity' => $interfaceEntity,
                'col' => $block->getCol()->getId(),
                'block' => $block->getId()
            ]);
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }

    /**
     * Edit Block
     *
     * @Route("/{col}/{interfaceName}/{interfaceEntity}/edit/{block}", methods={"GET", "POST"}, name="admin_block_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        /** @var Block $block */
        $block = $this->entityManager->getRepository(Block::class)->find($request->get('block'));
        if (!$block) {
            throw $this->createNotFoundException($this->translator->trans("Ce bloc n'existe pas !!", [], 'front'));
        }

        $blockTypeSlug = $block->getBlockType()->getSlug();

        if (isset(self::FORM_TYPES[$blockTypeSlug])) {
            $this->formType = self::FORM_TYPES[$blockTypeSlug];
        }

        if (!$this->formType) {
            foreach (self::FORM_TYPES_GROUPS as $group => $configuration) {
                if (preg_match('/' . $group . '/', $blockTypeSlug)) {
                    $this->formType = $configuration['formType'];
                    $this->template = 'admin/page/layout/field.html.twig';
                    break;
                }
            }
        }

        if ($blockTypeSlug === 'core-action') {
            $this->pageTitle = $this->translator->trans('Bloc :', [], 'admin') . ' ' . $this->translator->trans($block->getAction()->getSlug(), [], 'entity_action');
        } else {
            $this->pageTitle = $this->translator->trans('Bloc :', [], 'admin') . ' ' . $this->translator->trans($block->getBlockType()->getSlug(), [], 'entity_blocktype');
        }

        if ($blockTypeSlug === 'icon') {
            $this->arguments['stylesSrc'] = ['admin-icons-library' => 'admin'];
        }

        return parent::edit($request);
    }

    /**
     * Edit background color Block
     *
     * @Route("/background/{block}", name="admin_block_background", options={"expose"=true})
     *
     * @param Request $request
     * @return Response
     */
    public function background(Request $request)
    {
        $this->disableFlash = true;
        $this->template = 'admin/core/layout/background.html.twig';
        $this->formType = BackgroundColorBlockType::class;
        return parent::edit($request);
    }

    /**
     * Block[] positions update
     *
     * @Route("/positions/pack/{data}", methods={"GET"}, name="admin_blocks_positions", options={"expose"=true})
     *
     * @param BlockRepository $blockRepository
     * @param ColRepository $colRepository
     * @param string $data
     * @return JsonResponse
     */
    public function positions(BlockRepository $blockRepository, ColRepository $colRepository, string $data)
    {
        $blocksData = explode('&', $data);

        foreach ($blocksData as $colData) {

            $matchesId = explode('=', $colData);
            $matches = explode(',', urldecode($matchesId[1]));

            $block = $blockRepository->find($matchesId[0]);
            $col = $colRepository->find($matches[0]);
            $block->setPosition($matches[1]);
            $block->setCol($col);
            $this->entityManager->persist($block);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Set Col size
     *
     * @Route("/size/{block}/{size}", methods={"GET"}, name="admin_block_size", options={"expose"=true})
     * @IsGranted("ROLE_EDIT")
     *
     * @param Block $block
     * @param int $size
     * @return JsonResponse
     */
    public function size(Block $block, int $size)
    {
        $block->setSize($size);
        $this->entityManager->persist($block);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * Block modal
     *
     * @Route("/add/modal/{col}/{configuration}/{entityId}", name="admin_block_modal", options={"expose"=true})
     *
     * @param Col $col
     * @param LayoutConfiguration $configuration
     * @param int $entityId
     * @return JsonResponse
     */
    public function modal(Col $col, LayoutConfiguration $configuration, int $entityId)
    {
        return new JsonResponse(['html' => $this->renderView('admin/core/layout/new-block.html.twig', [
            'col' => $col,
            'blockTypeAction' => $this->entityManager->getRepository(BlockType::class)->findOneBySlug('core-action'),
            'entity' => $this->entityManager->getRepository($configuration->getEntity())->find($entityId),
            'interface' => $this->getInterface($configuration->getEntity()),
            'configuration' => $configuration
        ])]);
    }

    /**
     * Edit Block configuration
     *
     * @Route("/modal/configuration/{block}", name="admin_block_configuration")
     *
     * {@inheritdoc}
     */
    public function configuration(Request $request)
    {
        $this->disableFlash = true;
        $this->entity = $this->entityManager->getRepository(Block::class)->find($request->get('block'));
        $this->formType = BlockConfigurationType::class;
        $this->template = 'admin/core/layout/block-configuration.html.twig';
        $this->arguments['block'] = $this->entity;

        return parent::edit($request);
    }

    /**
     * Delete Block
     *
     * @Route("/{col}/delete/{block}", methods={"DELETE"}, name="admin_block_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Add GDPR field
     *
     * @param Website $website
     * @param FieldConfiguration $fieldConfiguration
     */
    private function addGdprField(Website $website, FieldConfiguration $fieldConfiguration)
    {
        $message = $this->translator->trans("En soumettant ce formulaire, vous acceptez que les informations saisies soient [utilisées, exploitées, traitées] pour permettre de [vous recontacter, pour vous envoyer la newsletter, dans le cadre de la relation commerciale qui découle de cette demande de devis].", [], 'admin');

        $valueI18n = new i18n();
        $valueI18n->setLocale($website->getConfiguration()->getLocale());
        $valueI18n->setWebsite($website);
        $valueI18n->setIntroduction($message);
        $valueI18n->setBody(true);

        $value = new FieldValue();
        $value->addI18n($valueI18n);

        $fieldConfiguration->addFieldValue($value);
    }
}