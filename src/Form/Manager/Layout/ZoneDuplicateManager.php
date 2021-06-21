<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Layout\Zone;
use App\Form\Manager\Core\BaseDuplicateManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * ZoneDuplicateManager
 *
 * Manage admin Zone duplication form
 *
 * @property EntityManagerInterface $entityManager
 * @property ColDuplicateManager $colDuplicateManager
 * @property LayoutManager $layoutManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneDuplicateManager extends BaseDuplicateManager
{
    protected $entityManager;
    private $colDuplicateManager;
    private $layoutManager;

    /**
     * ZoneDuplicateManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ColDuplicateManager $colDuplicateManager
     * @param LayoutManager $layoutManager
     */
    public function __construct(EntityManagerInterface $entityManager, ColDuplicateManager $colDuplicateManager, LayoutManager $layoutManager)
    {
        $this->entityManager = $entityManager;
        $this->colDuplicateManager = $colDuplicateManager;
        $this->layoutManager = $layoutManager;
    }

    /**
     * Execute duplication
     *
     * @param Zone $zone
     * @param Website $website
     * @param Form $form
     */
    public function execute(Zone $zone, Website $website, Form $form)
    {
        /** @var Page $destinationPage */
        $destinationPage = $form->get('page')->getData();
        $layout = $destinationPage->getLayout();
        $session = new Session();

        if(is_object($destinationPage) && method_exists($destinationPage, 'getWebsite')) {
            $session->set('DUPLICATE_TO_WEBSITE_FROM_ZONE', $destinationPage->getWebsite());
        }

        /** @var Zone $zoneToDuplicate */
        $zoneToDuplicate = $form->get('zone')->getData();

        $zone->setPosition(count($layout->getZones()) + 1);

        $this->addZone($zoneToDuplicate, $zone, $layout, $website);

        $this->layoutManager->setGridZone($layout);

        $session->remove('DUPLICATE_TO_WEBSITE_FROM_ZONE');
    }

    /**
     * Duplicate Zones
     *
     * @param Layout $layout
     * @param Collection $zonesToDuplicate
     * @param Website $website
     */
    public function addZones(Layout $layout, Collection $zonesToDuplicate, Website $website)
    {
        foreach ($zonesToDuplicate as $zoneToDuplicate) {
            $zone = new Zone();
            $zone->setPosition($zoneToDuplicate->getPosition());
            $this->addZone($zoneToDuplicate, $zone, $layout, $website);
        }
    }

    /**
     * Duplicate Zone
     *
     * @param Zone $zoneToDuplicate
     * @param Zone $zone
     * @param Layout $layout
     * @param Website $website
     */
    public function addZone(Zone $zoneToDuplicate, Zone $zone, Layout $layout, Website $website)
    {
        $zone->setFullSize($zoneToDuplicate->getFullSize());
        $zone->setStandardizeMedia($zoneToDuplicate->getStandardizeElements());
        $zone->setHide($zoneToDuplicate->getHide());
        $zone->setVerticalAlign($zoneToDuplicate->getVerticalAlign());
        $zone->setEndAlign($zoneToDuplicate->getEndAlign());
        $zone->setReverse($zoneToDuplicate->getReverse());
        $zone->setHideMobile($zoneToDuplicate->getHideMobile());
        $zone->setBackgroundColor($zoneToDuplicate->getBackgroundColor());
        $zone->setAlignment($zoneToDuplicate->getAlignment());
        $zone->setMobilePosition($zoneToDuplicate->getMobilePosition());
        $zone->setTabletPosition($zoneToDuplicate->getTabletPosition());
        $zone->setMarginTop($zoneToDuplicate->getMarginTop());
        $zone->setMarginRight($zoneToDuplicate->getMarginRight());
        $zone->setMarginBottom($zoneToDuplicate->getMarginBottom());
        $zone->setMarginLeft($zoneToDuplicate->getMarginLeft());
        $zone->setPaddingTop($zoneToDuplicate->getPaddingTop());
        $zone->setPaddingRight($zoneToDuplicate->getPaddingRight());
        $zone->setPaddingBottom($zoneToDuplicate->getPaddingBottom());
        $zone->setPaddingLeft($zoneToDuplicate->getPaddingLeft());
        $zone->setTransition($zoneToDuplicate->getTransition());
        $zone->setCustomId($zoneToDuplicate->getCustomId());
        $zone->setCustomClass($zoneToDuplicate->getCustomClass());
        $zone->setBackgroundFixed($zoneToDuplicate->getBackgroundFixed());
        $zone->setBackgroundParallax($zoneToDuplicate->getBackgroundParallax());
        $zone->setStandardizeMedia($zoneToDuplicate->getStandardizeMedia());
        $zone->setGrid($zoneToDuplicate->getGrid());
        $zone->setLayout($layout);

        $this->addMediaRelations($zone, $zoneToDuplicate->getMediaRelations());
        $this->addI18ns($zone, $zoneToDuplicate->getI18ns());

        $this->entityManager->persist($zone);
        $this->entityManager->flush();
        $this->entityManager->refresh($zone);

        $this->colDuplicateManager->addCols($zone, $zoneToDuplicate->getCols(), $website);
    }
}