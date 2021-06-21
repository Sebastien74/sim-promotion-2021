<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Newscast\Newscast;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * NewscastManager
 *
 * Manage admin Newscast form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastManager
{
    private $entityManager;

    /**
     * NewscastManager constructor.
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
     * @param Newscast $newscast
     * @param Website $website
     */
    public function prePersist(Newscast $newscast, Website $website)
    {
        $i18ns = $newscast->getI18ns();
        $this->setI18ns($website, $i18ns, $newscast);
        $this->setTitleForce($i18ns);
    }

    /**
     * Set i18ns
     *
     * @param Website $website
     * @param Collection $i18ns
     * @param Newscast $newscast
     */
    private function setI18ns(Website $website, Collection $i18ns, Newscast $newscast)
    {
        if ($i18ns->isEmpty()) {
            foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                $i18n = new i18n();
                $i18n->setLocale($locale);
                $i18n->setWebsite($website);
                $newscast->addI18n($i18n);
                $this->entityManager->persist($newscast);
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