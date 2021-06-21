<?php

namespace App\Form\EventListener\Media;

use App\Entity\Media\Media;
use App\Form\EventListener\BaseListener;
use Symfony\Component\Form\FormEvent;

/**
 * VideoListener
 *
 * Listen Video media
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class VideoListener extends BaseListener
{
    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var Media $entity */
        $entity = $event->getData();
        $formats = ['mp4', 'webm', 'ogv'];

        $entity->setScreen('poster');

        foreach ($formats as $format) {
            $existing = $this->screenExist($entity, $format);
            if (!$existing) {
                $this->addScreen($entity, $format);
            }
        }
    }

    /**
     * Check if media screen existing
     *
     * @param Media $media
     * @param string $format
     * @return bool
     */
    private function screenExist(Media $media, string $format)
    {
        foreach ($media->getMediaScreens() as $screen) {
            if ($screen->getScreen() === $format) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add screen Media
     *
     * @param Media $media
     * @param string $format
     */
    private function addScreen(Media $media, string $format)
    {
        $mediaFormat = new Media();
        $mediaFormat->setWebsite($this->website);
        $mediaFormat->setScreen($format);
        $media->addMediaScreen($mediaFormat);
    }
}