<?php

namespace App\Form\EventListener\Layout;

use App\Entity\Layout\FieldConfiguration;
use App\Entity\Translation\i18n;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * ValuesListener
 *
 * Listen Values Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ValuesListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var FieldConfiguration $entity */
        $entity = $event->getData();
        $values = $entity->getFieldValues();

        foreach ($values as $value) {
            foreach ($this->locales as $locale) {
                $exist = $this->localeExist($value, $locale);
                if (!$exist) {
                    $this->addI18n($locale, $value);
                }
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
     */
    private function addI18n(string $locale, $entity)
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setWebsite($this->website);
        $entity->addI18n($i18n);
    }
}