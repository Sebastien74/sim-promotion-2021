<?php

namespace App\Form\Manager\Information;

use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;

/**
 * InformationManager
 *
 * Manage admin SocialNetwork form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationManager
{
    private $entityManager;

    /**
     * InformationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @preUpdate
     *
     * @param Information $information
     * @param Website $website
     */
    public function preUpdate(Information $information, Website $website)
    {
        $this->synchronizeLocales($information, $website);
    }

    /**
     * @prePersist
     *
     * @param Information $information
     * @param Website $website
     */
    public function prePersist(Information $information, Website $website)
    {
        $this->synchronizeLocales($information, $website);
    }

    /**
     * Synchronize locale entities
     *
     * @param Information $information
     * @param Website $website
     */
    private function synchronizeLocales(Information $information, Website $website)
    {
        $configuration = $website->getConfiguration();
        $defaultLocale = $configuration->getLocale();
        $defaultI18n = $this->getDefaultI18n($information, $defaultLocale);

        if ($defaultI18n) {
            foreach ($information->getI18ns() as $i18n) {
                if ($i18n->getLocale() !== $defaultLocale) {
                    $this->synchronizeLocale($defaultI18n, $i18n);
                }
            }
        }
    }

    /**
     * Synchronize locale entity
     *
     * @param i18n $defaultI18n
     * @param i18n $i18n
     */
    private function synchronizeLocale(i18n $defaultI18n, i18n $i18n)
    {
        if (!$i18n->getTitle() && $defaultI18n->getTitle()) {
            $i18n->setTitle($defaultI18n->getTitle());
            $this->entityManager->persist($i18n);
        }
    }

    /**
     * Get default locale i18n
     *
     * @param Information $information
     * @param string $defaultLocale
     * @return i18n|null
     */
    private function getDefaultI18n(Information $information, string $defaultLocale)
    {
        foreach ($information->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $defaultLocale) {
                return $i18n;
            }
        }
    }
}