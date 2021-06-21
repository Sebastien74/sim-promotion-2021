<?php

namespace App\Form\EventListener\Translation;

use App\Entity\Translation\i18n;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * i18nsListener
 *
 * Listen i18n Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nsListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        $titleForce = !empty($this->options['fields_data']['titleForce'])
            ? $this->options['fields_data']['titleForce']
            : 2;

        if (!empty($entity)) {
            foreach ($this->locales as $locale) {
                $exist = $this->localeExist($entity, $locale);
                $defaultI18n = $this->getDefault($entity);
                if (!$exist) {
                    $titleForce = $defaultI18n && $defaultI18n->getTitleForce()
                        ? $defaultI18n->getTitleForce() : $titleForce;
                    $this->addI18n($locale, $entity, $titleForce);
                }
            }
        }
    }

    /**
     * Get default locale Media
     *
     * @param $entity
     * @return i18n|null
     */
    private function getDefault($entity)
    {
        foreach ($entity->getI18ns() as $existingI18n) {
            if ($existingI18n->getLocale() === $this->defaultLocale) {
                return $existingI18n;
            }
        }
    }

    /**
     * Check if i18n locale exist
     *
     * @param mixed $entity
     * @param string $locale
     * @return bool
     */
    private function localeExist($entity, string $locale)
    {
        foreach ($entity->getI18ns() as $existingI18n) {
            if ($existingI18n->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add i18n
     *
     * @param string $locale
     * @param mixed $entity
     * @param int $titleForce
     */
    private function addI18n(string $locale, $entity, int $titleForce)
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setWebsite($this->website);
        $i18n->setTitleForce($titleForce);
        $entity->addI18n($i18n);
    }
}