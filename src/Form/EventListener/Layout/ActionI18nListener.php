<?php

namespace App\Form\EventListener\Layout;

use App\Entity\Layout\ActionI18n;
use App\Entity\Layout\Block;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * ActionI18nListener
 *
 * Listen ActionI18n Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionI18nListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var Block $entity */
        $entity = $event->getData();

        if (!empty($entity)) {

            foreach ($this->locales as $locale) {

                $i18n = NULL;
                foreach ($entity->getActionI18ns() as $existingI18n) {
                    if ($existingI18n->getLocale() === $locale) {
                        $i18n = $existingI18n;
                    }
                }

                if (!$i18n) {
                    $i18n = new ActionI18n();
                    $i18n->setLocale($locale);
                    $entity->addActionI18n($i18n);
                }
            }
        }
    }
}