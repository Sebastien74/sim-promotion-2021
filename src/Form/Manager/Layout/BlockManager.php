<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\FieldValue;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Form\Form;

/**
 * BlockManager
 *
 * Manage admin Block form
 *
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 * @property string $blockType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockManager
{
    private $entityManager;
    private $website;
    private $blockType;

    /**
     * BlockManager constructor.
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
     * @param Block $block
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function preUpdate(Block $block, Website $website, array $interface, Form $form)
    {
        $blockTypeSlug = $block->getBlockType() instanceof BlockType ? $block->getBlockType()->getSlug() : NULL;
        $setter = 'set' . ucfirst(str_replace('-', '', $blockTypeSlug));
        if(method_exists($this, $setter)) {
            $this->$setter($block, $website);
        }

        $this->setFieldConfiguration($block, $website, $interface, $form);
    }

    /**
     * Set Block as Card
     *
     * @param Block $block
     * @param Website $website
     */
    private function setCard(Block $block, Website $website)
    {
        foreach ($block->getI18ns() as $i18n) {
            foreach ($block->getMediaRelations() as $mediaRelation) {
                if($mediaRelation->getLocale() === $i18n->getLocale()) {
                    $mediaRelationI18n = $mediaRelation->getI18n();
                    if(!$mediaRelationI18n instanceof i18n) {
                        $mediaRelationI18n = new i18n();
                        $mediaRelationI18n->setLocale($i18n->getLocale());
                        $mediaRelationI18n->setWebsite($website);
                        $mediaRelation->setI18n($mediaRelationI18n);
                    }
                    $mediaRelationI18n->setNewTab($i18n->getNewTab());
                    $mediaRelationI18n->setExternalLink($i18n->getExternalLink());
                    $this->entityManager->persist($mediaRelationI18n);
                }
            }
        }
    }

    /**
     * Set FieldConfiguration
     *
     * @param Block $block
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    private function setFieldConfiguration(Block $block, Website $website, array $interface, Form $form)
    {
        $fieldConfiguration = $block->getFieldConfiguration();
        $configuration = $website->getConfiguration();
        $defaultLocale = $configuration->getLocale();
        $this->website = $website;
        $this->blockType = $block->getBlockType() ? $block->getBlockType()->getSlug() : NULL;

        if ($fieldConfiguration) {

            if (!$fieldConfiguration->getBlock()) {
                $fieldConfiguration->setBlock($block);
            }

            $fieldValuePosition = 1;
            foreach ($fieldConfiguration->getFieldValues() as $value) {
                if ($value->getId()) {
                    $fieldValuePosition++;
                }
            }

            foreach ($fieldConfiguration->getFieldValues() as $value) {

                $value->setConfiguration($fieldConfiguration);
                if (!$value->getId()) {
                    $value->setPosition($fieldValuePosition);
                    $fieldValuePosition++;
                }

                foreach ($configuration->getAllLocales() as $locale) {
                    $exist = $this->localeExist($value, $locale);
                    if (!$exist) {
                        $this->addI18n($locale, $value, $defaultLocale);
                    }
                }
            }
        }
    }

    /**
     * Check if i18n locale exist
     *
     * @param FieldValue $value
     * @param string $locale
     * @return bool
     */
    private function localeExist(FieldValue $value, string $locale)
    {
        foreach ($value->getI18ns() as $existingI18n) {
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
     * @param FieldValue $value
     * @param string $defaultLocale
     */
    private function addI18n(string $locale, FieldValue $value, string $defaultLocale)
    {
        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setWebsite($this->website);

        if ($locale === $defaultLocale) {

            $body = $this->blockType === 'form-emails' && !preg_match('/@/', $value->getAdminName())
                ? Urlizer::urlize($value->getAdminName()) . '@email.com'
                : $value->getAdminName();

            $i18n->setIntroduction($value->getAdminName());
            $i18n->setBody($body);
        }

        $value->addI18n($i18n);
    }
}