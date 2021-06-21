<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\CssClass;
use App\Entity\Layout\Zone;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ZoneConfigurationManager
 *
 * Manage admin Zone CSS form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneConfigurationManager
{
    private $entityManager;

    /**
     * ZoneConfigurationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @onFlush
     *
     * @param Zone $zone
     * @param Website $website
     */
    public function onFlush(Zone $zone, Website $website)
    {
        $configuration = $website->getConfiguration();
        $classes = explode(' ', $zone->getCustomClass());

        foreach ($classes as $class) {

            if ($class) {

                $existing = $this->entityManager->getRepository(CssClass::class)->findOneBy([
                    'configuration' => $configuration,
                    'name' => $class
                ]);

                if (!$existing) {
                    $cssClass = new CssClass();
                    $cssClass->setConfiguration($configuration)
                        ->setName($class);
                    $this->entityManager->persist($cssClass);
                    $this->entityManager->flush();
                }
            }
        }
    }
}