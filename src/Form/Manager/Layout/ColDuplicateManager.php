<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Col;
use App\Entity\Layout\Zone;
use App\Form\Manager\Core\BaseDuplicateManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ColDuplicateManager
 *
 * Manage admin Col duplication form
 *
 * @property EntityManagerInterface $entityManager
 * @property BlockDuplicateManager $blockDuplicateManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColDuplicateManager extends BaseDuplicateManager
{
    protected $entityManager;
    private $blockDuplicateManager;

    /**
     * ColDuplicateManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param BlockDuplicateManager $blockDuplicateManager
     */
    public function __construct(EntityManagerInterface $entityManager, BlockDuplicateManager $blockDuplicateManager)
    {
        $this->entityManager = $entityManager;
        $this->blockDuplicateManager = $blockDuplicateManager;
    }

    /**
     * Duplicate Cols
     *
     * @param Zone $zone
     * @param Collection $colsToDuplicate
     * @param Website $website
     */
    public function addCols(Zone $zone, Collection $colsToDuplicate, Website $website)
    {
        foreach ($colsToDuplicate as $colToDuplicate) {

            /** @var Col $colToDuplicate */

            $col = new Col();
            $col->setFullSize($colToDuplicate->getFullSize());
            $col->setStandardizeElements($colToDuplicate->getStandardizeElements());
            $col->setHide($colToDuplicate->getHide());
            $col->setVerticalAlign($colToDuplicate->getVerticalAlign());
            $col->setEndAlign($colToDuplicate->getEndAlign());
            $col->setReverse($colToDuplicate->getReverse());
            $col->setHideMobile($colToDuplicate->getHideMobile());
            $col->setBackgroundColor($colToDuplicate->getBackgroundColor());
            $col->setAlignment($colToDuplicate->getAlignment());
            $col->setMobilePosition($colToDuplicate->getMobilePosition());
            $col->setTabletPosition($colToDuplicate->getTabletPosition());
            $col->setMarginTop($colToDuplicate->getMarginTop());
            $col->setMarginRight($colToDuplicate->getMarginRight());
            $col->setMarginBottom($colToDuplicate->getMarginBottom());
            $col->setMarginLeft($colToDuplicate->getMarginLeft());
            $col->setPaddingTop($colToDuplicate->getPaddingTop());
            $col->setPaddingRight($colToDuplicate->getPaddingRight());
            $col->setPaddingBottom($colToDuplicate->getPaddingBottom());
            $col->setPaddingLeft($colToDuplicate->getPaddingLeft());
            $col->setTransition($colToDuplicate->getTransition());
            $col->setSize($colToDuplicate->getSize());
            $col->setPosition($colToDuplicate->getPosition());
            $col->setMobileSize($colToDuplicate->getMobileSize());
            $col->setTabletSize($colToDuplicate->getTabletSize());
            $col->setBackgroundFullSize($colToDuplicate->getBackgroundFullSize());
            $col->setZone($zone);

            $this->addMediaRelations($col, $colToDuplicate->getMediaRelations());

            $this->entityManager->persist($col);
            $this->entityManager->flush();
            $this->entityManager->refresh($col);

            $this->blockDuplicateManager->addBlocks($col, $colToDuplicate->getBlocks());
        }
    }
}