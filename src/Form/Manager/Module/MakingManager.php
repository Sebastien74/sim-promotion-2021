<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Making\Making;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * MakingManager
 *
 * Manage admin Making form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MakingManager
{
    private $entityManager;

    /**
     * MakingManager constructor.
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
     * @param Making $making
     * @param Website $website
     */
    public function prePersist(Making $making, Website $website)
    {
        $i18ns = $making->getI18ns();
        $this->setI18ns($website, $i18ns, $making);
        $this->setTitleForce($i18ns);
    }

    /**
     * Set i18ns
     *
     * @param Website $website
     * @param Collection $i18ns
     * @param Making $making
     */
    private function setI18ns(Website $website, Collection $i18ns, Making $making)
    {
        if ($i18ns->isEmpty()) {
            foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                $i18n = new i18n();
                $i18n->setLocale($locale);
                $i18n->setWebsite($website);
                $making->addI18n($i18n);
                $this->entityManager->persist($making);
            }
        }
    }

    /**
     * Set title force to H1
     *
     * @param Collection $i18ns
     */
    private function setTitleForce(Collection $i18ns)
    {
        foreach ($i18ns as $i18n) {
            $i18n->setTitleForce(1);
            $this->entityManager->persist($i18n);
        }
    }
}