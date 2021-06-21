<?php

namespace App\Form\Manager\Layout;

use App\Entity\Layout\Col;
use App\Entity\Layout\Grid;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Zone;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;

/**
 * ZoneManager
 *
 * Manage admin Zone form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneManager
{
    private $entityManager;

    /**
     * ZoneManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Zone
     *
     * @param Layout $layout
     * @param Form $form
     */
    public function add(Layout $layout, Form $form)
    {
        $zone = new Zone();
        $zone->setPosition(count($layout->getZones()) + 1);

        $layout->addZone($zone);

        /** @var Grid $grid */
        $grid = $form->getData()['grid'];

        if ($grid) {

            foreach ($grid->getCols() as $key => $gridCol) {

                $position = $key + 1;

                $col = new Col();
                $col->setPosition($position);
                $col->setSize($gridCol->getSize());

                $zone->addCol($col);
            }

            $this->entityManager->persist($layout);
            $this->entityManager->flush();
        }

        $this->addI18ns($layout, $zone);
    }

    /**
     * Add i18n[] to Zone
     *
     * @param Layout $layout
     * @param Zone $zone
     */
    private function addI18ns(Layout $layout, Zone $zone)
    {
        $website = $layout->getWebsite();
        $locales = $website->getConfiguration()->getLocales();

        foreach ($locales as $locale) {
            $i18n = new i18n();
            $i18n->setWebsite($website);
            $i18n->setLocale($locale);
            $zone->addI18n($i18n);
        }
    }
}