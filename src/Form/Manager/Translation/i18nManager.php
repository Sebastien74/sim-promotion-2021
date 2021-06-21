<?php

namespace App\Form\Manager\Translation;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;

/**
 * i18nManager
 *
 * Manage i18n admin form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nManager
{
    private $entityManager;

    /**
     * i18nManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Post i18n
     *
     * @param Form $form
     * @param Website $website
     */
    public function post(Form $form, Website $website)
    {
        $entity = $form->getData();
        $defaultLocale = $website->getConfiguration()->getLocale();

        if (method_exists($entity, 'getI18ns')) {

            $defaultI18n = $this->getDefaultI18n($entity, $defaultLocale);

            if ($defaultI18n) {
                foreach ($entity->getI18ns() as $i18n) {
                    /** @var $i18n i18n */
                    if ($i18n->getLocale() !== $defaultLocale) {
                        $this->setTitleForce($i18n, $defaultI18n);
                        $this->setTitleAlignment($i18n, $defaultI18n);
                        $this->setIntroductionAlignment($i18n, $defaultI18n);
                        $this->setBodyAlignment($i18n, $defaultI18n);
                    } elseif (!$i18n->getId() && method_exists($entity, 'getAdminName') && $entity->getAdminName()) {
                        $i18n->setTitle($entity->getAdminName());
                    }
                }
            } elseif (method_exists($entity, 'getAdminName') && $entity->getAdminName()) {
                $i18n = new i18n();
                $i18n->setLocale($defaultLocale);
                $i18n->setTitle($entity->getAdminName());
                $i18n->setWebsite($website);
                $entity->addI18n($i18n);
            }
        } elseif (method_exists($entity, 'getI18n') && method_exists($entity, 'getLocale') && !$entity->getI18n()->getLocale()) {
            /** @var $i18n i18n */
            $i18n = $entity->getI18n();
            $i18n->setLocale($entity->getLocale());
        }
    }

    /**
     * Synchronize locale i18n before Form render
     *
     * @param mixed $entity
     * @param Website $website
     */
    public function synchronizeLocales($entity, Website $website)
    {
        $configuration = $website->getConfiguration();
        $defaultLocale = $configuration->getLocale();

        if (method_exists($entity, 'getI18ns')) {

            $defaultI18n = $this->getDefaultI18n($entity, $defaultLocale);
            if (!$defaultI18n && $entity instanceof Block) {
                $defaultI18n = $this->addI18n($website, $entity, $defaultLocale, $defaultLocale, NULL);
            }

            foreach ($configuration->getAllLocales() as $locale) {
                if ($locale !== $defaultLocale) {
                    $existing = $this->localeExist($entity, $locale);
                    if (!$existing && $defaultI18n) {
                        $this->addI18n($website, $entity, $locale, $defaultLocale, $defaultI18n);
                    } elseif ($existing && !$existing->getId()) {
                        $this->entityManager->persist($existing);
                        $this->entityManager->flush();
                        $this->entityManager->refresh($existing);
                    }
                }
            }
        }
    }

    private function addI18n(Website $website, $entity, string $locale, string $defaultLocale, i18n $defaultI18n = NULL)
    {
        $referI18n = $defaultI18n ? $defaultI18n : new i18n();
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setWebsite($website);

        if ($entity instanceof Block && $locale === $defaultLocale && $entity->getBlockType()->getSlug() === 'titleheader') {
            $i18n->setTitleForce(1);
        } else {
            $this->setTitleForce($i18n, $referI18n);
        }

        $this->setTitleAlignment($i18n, $referI18n);
        $this->setIntroductionAlignment($i18n, $referI18n);
        $this->setBodyAlignment($i18n, $referI18n);
        $entity->addI18n($i18n);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);

        return $i18n;
    }

    /**
     * Get default locale i18n
     *
     * @param $entity
     * @param string $defaultLocale
     * @return i18n|bool
     */
    private function getDefaultI18n($entity, string $defaultLocale)
    {
        foreach ($entity->getI18ns() as $i18n) {
            /** @var $i18n i18n */
            if ($i18n->getLocale() === $defaultLocale) {
                return $i18n;
            }
        }
    }

    /**
     * Check if i18n locale exist
     *
     * @param mixed $entity
     * @param string $locale
     * @return bool|i18n
     */
    private function localeExist($entity, string $locale)
    {
        foreach ($entity->getI18ns() as $existingI18n) {
            if ($existingI18n->getLocale() === $locale) {
                return $existingI18n;
            }
        }

        return false;
    }

    /**
     * Set title force
     *
     * @param i18n $i18n
     * @param i18n $defaultI18n
     */
    private function setTitleForce(i18n $i18n, i18n $defaultI18n)
    {
        if ($defaultI18n->getTitleForce() && !$i18n->getTitleForce()) {
            $i18n->setTitleForce($defaultI18n->getTitleForce());
            $this->entityManager->persist($i18n);
        }
    }

    /**
     * Set title alignment
     *
     * @param i18n $i18n
     * @param i18n $defaultI18n
     */
    private function setTitleAlignment(i18n $i18n, i18n $defaultI18n)
    {
        if ($defaultI18n->getTitleAlignment() && !$i18n->getTitleAlignment()) {
            $i18n->setTitleAlignment($defaultI18n->getTitleAlignment());
            $this->entityManager->persist($i18n);
        }
    }

    /**
     * Set introduction alignment
     *
     * @param i18n $i18n
     * @param i18n $defaultI18n
     */
    private function setIntroductionAlignment(i18n $i18n, i18n $defaultI18n)
    {
        if ($defaultI18n->getIntroductionAlignment() && !$i18n->getIntroductionAlignment()) {
            $i18n->setIntroductionAlignment($defaultI18n->getIntroductionAlignment());
            $this->entityManager->persist($i18n);
        }
    }

    /**
     * Set body alignment
     *
     * @param i18n $i18n
     * @param i18n $defaultI18n
     */
    private function setBodyAlignment(i18n $i18n, i18n $defaultI18n)
    {
        if ($defaultI18n->getBodyAlignment() && !$i18n->getBodyAlignment()) {
            $i18n->setBodyAlignment($defaultI18n->getBodyAlignment());
            $this->entityManager->persist($i18n);
        }
    }
}