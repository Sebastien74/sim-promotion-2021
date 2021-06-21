<?php

namespace App\Form\EventListener\Translation;

use App\Entity\Translation\i18n;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * i18nListener
 *
 * Listen i18n Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $session = new Session();
        $entity = $event->getData();

        if (empty($entity->getI18n())) {
            $i18n = new i18n();
            $i18n->setWebsite($this->website);
            $i18n->setLocale($session->get('currentEntityLocale'));
            $entity->setI18n($i18n);
        }
    }
}