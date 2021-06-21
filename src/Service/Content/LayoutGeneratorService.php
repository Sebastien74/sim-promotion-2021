<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use App\Entity\Layout as Layout;
use Doctrine\ORM\EntityManagerInterface;

/**
 * LayoutGeneratorService
 *
 * Layout generation management
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutGeneratorService
{
    private $entityManager;

    /**
     * InformationService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Layout
     *
     * @param Website $website
     * @param array $options
     * @return Layout\Layout
     */
    public function addLayout(Website $website, array $options = []): Layout\Layout
    {
        $position = count($this->entityManager->getRepository(Layout\Layout::class)->findBy(['website' => $website])) + 1;
        $layout = new Layout\Layout();
        $layout->setPosition($position);
        $layout->setWebsite($website);

        foreach ($options as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if(method_exists($layout, $setter)) {
                $layout->$setter($value);
            }
        }

        $this->entityManager->persist($layout);

        return $layout;
    }

    /**
     * Add Zone
     *
     * @param Layout\Layout $layout
     * @param array $options
     * @return Layout\Zone
     */
    public function addZone(Layout\Layout $layout, array $options = []): Layout\Zone
    {
        $zone = new Layout\Zone();
        $layout->addZone($zone);
        $zone->setPosition($layout->getZones()->count());

        foreach ($options as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if(method_exists($zone, $setter)) {
                $zone->$setter($value);
            }
        }

        $this->entityManager->persist($layout);

        return $zone;
    }

    /**
     * Add Col
     *
     * @param Layout\Zone $zone
     * @param array $options
     * @return Layout\Col
     */
    public function addCol(Layout\Zone $zone, array $options = []): Layout\Col
    {
        $col = new Layout\Col();
        $zone->addCol($col);
        $col->setPosition($zone->getCols()->count());

        foreach ($options as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if(method_exists($col, $setter)) {
                $col->$setter($value);
            }
        }

        $this->entityManager->persist($zone);

        return $col;
    }

    /**
     * Add Block
     *
     * @param Layout\Col $col
     * @param array $options
     * @return Layout\Block
     */
    public function addBlock(Layout\Col $col, array $options = []): Layout\Block
    {
        $blockTypeRepository = $this->entityManager->getRepository(Layout\BlockType::class);

        $block = new Layout\Block();
        $col->addBlock($block);
        $block->setPosition($col->getBlocks()->count());

        if(!empty($options['blockType']) && $options['blockType'] === 'layout-titleheader') {
            $block->setPaddingLeft('ps-0');
            $block->setPaddingRight('pe-0');
        }

        foreach ($options as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if(method_exists($block, $setter)) {
                if($setter === 'setBlockType') {
                    $value = $blockTypeRepository->findOneBy(['slug' => $value]);
                }
                $block->$setter($value);
            }
        }

        $this->entityManager->persist($col);

        return $block;
    }

    /**
     * Flush
     */
    public function flush()
    {
        $this->entityManager->flush();
    }
}