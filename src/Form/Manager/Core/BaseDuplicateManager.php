<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Website;
use App\Entity\Media\MediaRelation;
use App\Entity\Translation\i18n;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * BaseDuplicateManager
 *
 * Manage admin form duplication
 *
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseDuplicateManager
{
    private const DISABLE_DUPLICATION_MEDIA = false;

    protected $entityManager;
    protected $website;

    /**
     * Duplicate MediaRelations
     *
     * @param mixed $entity
     * @param Collection $mediaRelationsToDuplicate
     */
    protected function addMediaRelations($entity, Collection $mediaRelationsToDuplicate)
    {
        $session = new Session();
        $duplicateToWebsiteSession = $session->get('DUPLICATE_TO_WEBSITE');
        $duplicateToWebsite = $duplicateToWebsiteSession instanceof Website ? $duplicateToWebsiteSession->getConfiguration()->getDuplicateMediasStatus() : self::DISABLE_DUPLICATION_MEDIA;
        $duplicateToWebsiteFromZoneSession = $session->get('DUPLICATE_TO_WEBSITE_FROM_ZONE');
        $duplicateToWebsiteFromZone = $duplicateToWebsiteFromZoneSession instanceof Website ? $duplicateToWebsiteFromZoneSession->getConfiguration()->getDuplicateMediasStatus() : self::DISABLE_DUPLICATION_MEDIA;

        if ($duplicateToWebsite || $duplicateToWebsiteFromZone) {

            foreach ($mediaRelationsToDuplicate as $mediaRelationToDuplicate) {

                /** @var MediaRelation $mediaRelationToDuplicate */

                $mediaRelation = new MediaRelation();
                $mediaRelation->setLocale($mediaRelationToDuplicate->getLocale());
                $mediaRelation->setCategory($mediaRelationToDuplicate->getCategory());
                $mediaRelation->setDisplayTitle($mediaRelationToDuplicate->getDisplayTitle());
                $mediaRelation->setPopup($mediaRelationToDuplicate->getPopup());
                $mediaRelation->setMaxWidth($mediaRelationToDuplicate->getMaxWidth());
                $mediaRelation->setPosition($mediaRelationToDuplicate->getPosition());
                $mediaRelation->setDownloadable($mediaRelationToDuplicate->getDownloadable());
                $mediaRelation->setMedia($mediaRelationToDuplicate->getMedia());

                $entity->addMediaRelation($mediaRelation);

                $i18n = $this->addI18n($mediaRelation->getLocale(), $mediaRelationToDuplicate->getI18n());
                $mediaRelation->setI18n($i18n);

                $this->entityManager->persist($entity);
            }
        }
    }

    /**
     * Duplicate i18ns
     *
     * @param mixed $entity
     * @param Collection $i18nsToDuplicate
     */
    protected function addI18ns($entity, Collection $i18nsToDuplicate)
    {
        foreach ($i18nsToDuplicate as $i18nToDuplicate) {
            /** @var i18n $i18nToDuplicate */
            $i18n = $this->addI18n($i18nToDuplicate->getLocale(), $i18nToDuplicate);
            $entity->addI18n($i18n);
        }
    }

    /**
     * Duplicate i18n
     *
     * @param string $locale
     * @param i18n|null $i18nToDuplicate
     * @return i18n
     */
    protected function addI18n(string $locale, i18n $i18nToDuplicate = NULL): i18n
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);

        if (!empty($i18nToDuplicate)) {
            $i18n->setTitle($i18nToDuplicate->getTitle());
            $i18n->setTitleForce($i18nToDuplicate->getTitleForce());
            $i18n->setSubTitle($i18nToDuplicate->getSubTitle());
            $i18n->setSubTitlePosition($i18nToDuplicate->getSubTitlePosition());
            $i18n->setTargetAlignment($i18nToDuplicate->getTargetAlignment());
            $i18n->setIntroduction($i18nToDuplicate->getIntroduction());
            $i18n->setIntroductionAlignment($i18nToDuplicate->getIntroductionAlignment());
            $i18n->setBody($i18nToDuplicate->getBody());
            $i18n->setBodyAlignment($i18nToDuplicate->getBodyAlignment());
            $i18n->setTargetLink($i18nToDuplicate->getTargetLink());
            $i18n->setTargetLabel($i18nToDuplicate->getTargetLabel());
            $i18n->setTargetAlignment($i18nToDuplicate->getTargetAlignment());
            $i18n->setTargetStyle($i18nToDuplicate->getTargetStyle());
            $i18n->setNewTab($i18nToDuplicate->getNewTab());
            $i18n->setExternalLink($i18nToDuplicate->getExternalLink());
            $i18n->setTargetPage($i18nToDuplicate->getTargetPage());
        }

        $session = new Session();
        $websiteSession = $session->get('DUPLICATE_TO_WEBSITE');
        $website = $websiteSession instanceof Website ? $websiteSession : (!empty($i18nToDuplicate) ? $i18nToDuplicate->getWebsite() : $this->website);
        $i18n->setWebsite($website);

        $this->entityManager->persist($i18n);

        return $i18n;
    }
}