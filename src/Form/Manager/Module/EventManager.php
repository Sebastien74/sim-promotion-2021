<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Event\Event;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * EventManager
 *
 * Manage admin Event form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EventManager
{
    private $entityManager;

    /**
     * EventManager constructor.
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
     * @param Event $event
     * @param Website $website
     */
    public function prePersist(Event $event, Website $website)
    {
        $i18ns = $event->getI18ns();
        $this->setI18ns($website, $i18ns, $event);
        $this->setTitleForce($i18ns);
    }

    /**
     * Set i18ns
     *
     * @param Website $website
     * @param Collection $i18ns
     * @param Event $event
     */
    private function setI18ns(Website $website, Collection $i18ns, Event $event)
    {
        if ($i18ns->isEmpty()) {
            foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                $i18n = new i18n();
                $i18n->setLocale($locale);
                $i18n->setWebsite($website);
                $event->addI18n($i18n);
                $this->entityManager->persist($event);
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