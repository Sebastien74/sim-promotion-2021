<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout as Layout;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;

/**
 * LayoutManager
 *
 * Manage admin Layout form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutManager
{
    private $entityManager;

    /**
     * LayoutManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Post Layout
     *
     * @param array $interface
     * @param Form $form
     * @param Website $website
     */
    public function post(array $interface, Form $form, Website $website)
    {
        $entity = $form->getData();
        $this->setLayout($interface, $entity, $website);
        $this->setLayoutUpdatedAt($entity);
    }

    /**
     * To create Layout if not exist
     *
     * @param array $interface
     * @param mixed $entity
     * @param Website $website
     */
    public function setLayout(array $interface, $entity, Website $website)
    {
        $haveInterface = !empty($interface['name']);
        $hasFlush = $entity->getId();
        $hasLayout = method_exists($entity, 'getLayout');
        $hasCustomLayout = $hasLayout && method_exists($entity, 'getCustomLayout') && $entity->getCustomLayout();
        $haveLayout = $hasLayout && $entity->getLayout();

        /** To add Layout */
        if (!$hasFlush && $hasLayout && !$hasCustomLayout && !$haveLayout && $haveInterface
            || $hasFlush && $hasLayout && $hasCustomLayout && !$haveLayout && $haveInterface) {

            $layout = new Layout\Layout();
            $layout->setWebsite($interface['website']);
            $layout->setAdminName($entity->getAdminName());
            $entity->setLayout($layout);

            if ($entity instanceof Layout\Page) {
                $this->addZone($layout, $website, $entity);
            }

            $this->entityManager->persist($layout);
        }
    }

    /**
     * Set Layout updatedAt and parent entity updatedAt
     *
     * @param null|mixed $entity
     */
    private function setLayoutUpdatedAt($entity = NULL)
    {
        $entityLayout = NULL;
        if ($entity instanceof Layout\Zone) {
            $entityLayout = $entity->getLayout();
        } elseif ($entity instanceof Layout\Col) {
            $entityLayout = $entity->getZone()->getLayout();
        } elseif ($entity instanceof Layout\Block) {
            $entityLayout = $entity->getCol()->getZone()->getLayout();
        }
        if ($entityLayout instanceof Layout\Layout) {
            $entityLayout->setUpdatedAt(new \DateTime('now'));
            $entityLayout->setParent($this->entityManager);
            $this->entityManager->persist($entityLayout);
        }
    }

    /**
     * Set gird zone for front class mapping
     *
     * @param Layout\Layout|null $layout
     */
    public function setGridZone(Layout\Layout $layout = NULL)
    {
        if (!$layout) {
            return;
        }

        $flush = false;

        foreach ($layout->getZones() as $zone) {

            $count = 0;
            $rows = [];
            $rowCount = 0;

            foreach ($zone->getCols() as $col) {
                $count = $count + $col->getSize();
                if ($count > 12) {
                    $count = intval($col->getSize());
                    $rowCount++;
                }
                $rows[$rowCount][$col->getId()] = $col->getSize();
            }

            $grids = [];
            foreach ($rows as $cols) {

                $class = '';
                $colsArray = [];
                foreach ($cols as $colId => $size) {
                    $class .= $size . '-';
                    $colsArray[] = $colId;
                }

                $grids[] = (object)[
                    'grid' => rtrim($class, '-'),
                    'cols' => $colsArray
                ];
            }

            $colsGrids = [];
            foreach ($grids as $grid) {
                foreach ($grid->cols as $col) {
                    $colsGrids[$col] = $grid->grid;
                }
            }

            if ($zone->getGrid() !== $colsGrids) {
                $flush = true;
                $zone->setGrid($colsGrids);
                $this->entityManager->persist($zone);
            }

        }

        if ($flush) {
            $this->entityManager->persist($layout);
            $this->entityManager->flush();
        }
    }

    /**
     * Add Zone Layout
     *
     * @param Layout\Layout $layout
     * @param Website $website
     * @param mixed $entity
     */
    private function addZone(Layout\Layout $layout, Website $website, $entity)
    {
        $zone = new Layout\Zone();
        $zone->setFullSize(true);
        $zone->setPaddingTop('pt-0');
        $zone->setPaddingBottom('pb-0');

        $layout->addZone($zone);

        $this->addCol($zone, $website, $entity);
    }

    /**
     * Add Col
     *
     * @param Layout\Zone $zone
     * @param Website $website
     * @param mixed $entity
     */
    private function addCol(Layout\Zone $zone, Website $website, $entity)
    {
        $col = new Layout\Col();
        $col->setPaddingRight('pe-0');
        $col->setPaddingLeft('ps-0');

        $zone->addCol($col);

        $this->addBlock($col, $website, $entity);
    }

    /**
     * Add Block
     *
     * @param Layout\Col $col
     * @param Website $website
     * @param mixed $entity
     */
    private function addBlock(Layout\Col $col, Website $website, $entity)
    {
        $block = new Layout\Block();
        $col->addBlock($block);

        $i18n = new i18n();
        $i18n->setTitle($entity->getAdminName());
        $i18n->setLocale($website->getConfiguration()->getLocale());
        $i18n->setWebsite($website);
        $i18n->setTitleForce(1);

        $block->addI18n($i18n);

        $blockType = $this->entityManager->getRepository(Layout\BlockType::class)->findOneBySlug('titleheader');
        $block->setBlockType($blockType);
    }
}