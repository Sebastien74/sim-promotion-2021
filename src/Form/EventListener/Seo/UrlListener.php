<?php

namespace App\Form\EventListener\Seo;

use App\Entity\Seo\Url;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * UrlListener
 *
 * Listen Url Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();

        foreach ($this->locales as $locale) {

            $exist = false;
            foreach ($entity->getUrls() as $url) {
                if ($url->getLocale() === $locale) {
                    $exist = true;
                }
            }

            if (!$exist && empty($entity->getId()) && $locale === $this->defaultLocale
                || !$exist && $entity->getId()) {
                $url = new Url();
                $url->setLocale($locale);
                $entity->addUrl($url);
            }
        }
    }
}