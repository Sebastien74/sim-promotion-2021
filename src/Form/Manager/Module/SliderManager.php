<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Slider\Slider;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;

/**
 * SliderManager
 *
 * Manage admin Form form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SliderManager
{
    private $entityManager;

    /**
     * SliderManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param Slider $slider
     * @param Website $website
     */
    public function prePersist(Slider $slider, Website $website)
    {
        if ($slider->getBanner()) {
            $slider->setIntervalDuration(15000);
        }
    }
}