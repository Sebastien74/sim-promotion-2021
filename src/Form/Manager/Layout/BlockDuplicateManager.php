<?php

namespace App\Form\Manager\Layout;

use App\Entity\Layout\Action;
use App\Entity\Layout\ActionI18n;
use App\Entity\Layout\Block;
use App\Entity\Layout\Col;
use App\Entity\Layout\FieldConfiguration;
use App\Entity\Layout\FieldValue;
use App\Form\Manager\Core\BaseDuplicateManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * BlockDuplicateManager
 *
 * Manage admin Block duplication form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockDuplicateManager extends BaseDuplicateManager
{
    protected $entityManager;

    /**
     * BlockDuplicateManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Duplicate Blocks
     *
     * @param Col $col
     * @param Collection $blocksToDuplicate
     */
    public function addBlocks(Col $col, Collection $blocksToDuplicate)
    {
        foreach ($blocksToDuplicate as $blockToDuplicate) {

            /** @var Block $blockToDuplicate */

            $block = new Block();
            $block->setCol($col);
            $block->setFullSize($blockToDuplicate->getFullSize());
            $block->setStandardizeElements($blockToDuplicate->getStandardizeElements());
            $block->setHide($blockToDuplicate->getHide());
            $block->setVerticalAlign($blockToDuplicate->getVerticalAlign());
            $block->setEndAlign($blockToDuplicate->getEndAlign());
            $block->setReverse($blockToDuplicate->getReverse());
            $block->setHideMobile($blockToDuplicate->getHideMobile());
            $block->setBackgroundColor($blockToDuplicate->getBackgroundColor());
            $block->setAlignment($blockToDuplicate->getAlignment());
            $block->setMobilePosition($blockToDuplicate->getMobilePosition());
            $block->setTabletPosition($blockToDuplicate->getTabletPosition());
            $block->setMarginTop($blockToDuplicate->getMarginTop());
            $block->setMarginRight($blockToDuplicate->getMarginRight());
            $block->setMarginBottom($blockToDuplicate->getMarginBottom());
            $block->setMarginLeft($blockToDuplicate->getMarginLeft());
            $block->setPaddingTop($blockToDuplicate->getPaddingTop());
            $block->setPaddingRight($blockToDuplicate->getPaddingRight());
            $block->setPaddingBottom($blockToDuplicate->getPaddingBottom());
            $block->setPaddingLeft($blockToDuplicate->getPaddingLeft());
            $block->setTransition($blockToDuplicate->getTransition());
            $block->setSize($blockToDuplicate->getSize());
            $block->setMobileSize($blockToDuplicate->getMobileSize());
            $block->setTabletSize($blockToDuplicate->getTabletSize());
            $block->setHeight($blockToDuplicate->getHeight());
            $block->setTemplate($blockToDuplicate->getTemplate());
            $block->setColor($blockToDuplicate->getColor());
            $block->setIcon($blockToDuplicate->getIcon());
            $block->setIconSize($blockToDuplicate->getIconSize());
            $block->setScript($blockToDuplicate->getScript());
            $block->setAutoplay($blockToDuplicate->getAutoplay());
            $block->setControls($blockToDuplicate->getControls());
            $block->setSoundControls($blockToDuplicate->getSoundControls());
            $block->setBlockType($blockToDuplicate->getBlockType());
            $block->setAction($blockToDuplicate->getAction());
            $block->setPosition($blockToDuplicate->getPosition());

            $this->setFieldConfiguration($blockToDuplicate, $block);
            $this->addActionI18ns($block, $blockToDuplicate->getActionI18ns());
            $this->addI18ns($block, $blockToDuplicate->getI18ns());
            $this->addMediaRelations($block, $blockToDuplicate->getMediaRelations());

            $this->entityManager->persist($block);
            $this->entityManager->flush();
            $this->entityManager->refresh($block);
        }

        $this->entityManager->flush();
    }

    /**
     * Set FieldConfiguration
     *
     * @param Block $blockToDuplicate
     * @param Block $block
     * @return FieldConfiguration|null
     */
    private function setFieldConfiguration(Block $blockToDuplicate, Block $block)
    {
        $fieldConfiguration = $blockToDuplicate->getFieldConfiguration();

        if ($fieldConfiguration) {

            $configuration = new FieldConfiguration();
            $configuration->setConstraints($fieldConfiguration->getConstraints());
            $configuration->setPreferredChoices($fieldConfiguration->getPreferredChoices());
            $configuration->setRequired($fieldConfiguration->getRequired());
            $configuration->setMultiple($fieldConfiguration->getMultiple());
            $configuration->setExpanded($fieldConfiguration->getExpanded());
            $configuration->setPicker($fieldConfiguration->getPicker());
            $configuration->setRegex($fieldConfiguration->getRegex());
            $configuration->setMin($fieldConfiguration->getMin());
            $configuration->setMax($fieldConfiguration->getMax());
            $configuration->setMaxFileSize($fieldConfiguration->getMaxFileSize());
            $configuration->setFilesTypes($fieldConfiguration->getFilesTypes());
            $configuration->setButtonType($fieldConfiguration->getButtonType());
            $configuration->setBlock($block);

            foreach ($fieldConfiguration->getFieldValues() as $fieldValueToDuplicate) {

                $fieldValue = new FieldValue();
                $fieldValue->setAdminName($fieldValueToDuplicate->getAdminName());
                $fieldValue->setConfiguration($configuration);

                $this->addI18ns($fieldValue, $fieldValueToDuplicate->getI18ns());

                $this->entityManager->persist($fieldValue);
            }

            $block->setFieldConfiguration($configuration);
            $this->entityManager->persist($block);

            return $configuration;
        }
    }

    /**
     * Add ActionI18n[]
     *
     * @param Block $block
     * @param Collection $actionsToDuplicate
     */
    private function addActionI18ns(Block $block, Collection $actionsToDuplicate)
    {
        $websiteToDuplicate = $block->getCol()->getZone()->getLayout()->getWebsite();

        foreach ($actionsToDuplicate as $actionToDuplicate) {

            /** @var ActionI18n $actionToDuplicate */

            $actionFilter = $actionToDuplicate->getActionFilter();
            $actionI18n = new ActionI18n();
            $actionI18n->setLocale($actionToDuplicate->getLocale());
            $websiteOrigin = $actionToDuplicate->getBlock()->getCol()->getZone()->getLayout()->getWebsite();

            if($websiteOrigin->getId() !== $websiteToDuplicate->getId()
                && $actionFilter
                && $block->getAction() instanceof Action) {

                $classname = $block->getAction()->getEntity();
                $repository = $this->entityManager->getRepository($classname);
                $originAction = $repository->find($actionFilter);

                if(is_object($originAction) && method_exists($originAction, 'getSlug') && method_exists($originAction, 'getWebsite')) {
                    $duplicateActions = $repository->findBy(['slug' => $originAction->getSlug(), 'website' => $websiteToDuplicate]);
                    $duplicateAction = !empty($duplicateActions[0]) ? $duplicateActions[0] : NULL;
                    if(is_object($duplicateAction) && method_exists($originAction, 'getId')) {
                        $actionFilter = $duplicateAction->getId();
                    }
                }
            }

            $actionI18n->setActionFilter($actionFilter);

            $block->addActionI18n($actionI18n);

            $this->entityManager->persist($actionI18n);
        }
    }
}