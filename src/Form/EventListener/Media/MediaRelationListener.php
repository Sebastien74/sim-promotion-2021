<?php

namespace App\Form\EventListener\Media;

use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * MediaRelationListener
 *
 * Listen MediaRelation Form attribute
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelationListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        $defaultMedia = $this->getDefault($entity);

        foreach ($this->locales as $locale) {
            $exist = $this->localeExist($entity, $locale);
            if (!$exist) {
                $this->addMedia($locale, $entity, $defaultMedia);
            }
        }
    }

    /**
     * Get default locale Media
     *
     * @param mixed $entity
     * @return Media|null
     */
    private function getDefault($entity)
    {
        foreach ($entity->getMediaRelations() as $relation) {
            /** @var MediaRelation $relation */
            if ($relation->getLocale() === $this->defaultLocale) {
                return $relation->getMedia();
            }
        }
    }

    /**
     * Check if MediaRelation locale existing
     *
     * @param mixed $entity
     * @param string $locale
     * @return bool
     */
    private function localeExist($entity, string $locale)
    {
        foreach ($entity->getMediaRelations() as $relation) {
            /** @var MediaRelation $relation */
            if ($relation->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add Media
     *
     * @param string $locale
     * @param mixed $entity
     * @param Media|null $defaultMedia
     */
    private function addMedia(string $locale, $entity, Media $defaultMedia = null)
    {
        $media = !$defaultMedia ? new Media() : $defaultMedia;
        if (!$media->getWebsite()) {
            $media->setWebsite($this->website);
        }

        $mediaRelation = new MediaRelation();
        $mediaRelation->setLocale($locale);
        $mediaRelation->setMedia($media);
        $entity->addMediaRelation($mediaRelation);
    }
}