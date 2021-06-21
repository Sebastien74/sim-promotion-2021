<?php

namespace App\DataFixtures;

use App\Entity\Layout\BlockType;
use App\Entity\Security\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as Type;

/**
 * BlockTypeFixture
 *
 * BlockType Fixture management
 *
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockTypeFixture extends BaseFixture implements DependentFixtureInterface
{
    private $position = 1;

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        $formBlocks = $this->getFormBocks();
        $layoutBlocks = $this->getLayoutBocks();
        $contentBlocks = $this->getContentBlocks();

        $blocks = array_merge($formBlocks, $layoutBlocks, $contentBlocks);

        foreach ($blocks as $config) {
            $blockType = $this->addBlockType($config);
            $this->addReference($blockType->getSlug(), $blockType);
        }
    }

    /**
     * Generate BlockType
     *
     * @param array $config
     * @return BlockType
     */
    private function addBlockType(array $config): BlockType
    {
        /** @var User $user */
        $user = $this->getReference('webmaster');

        $blockType = new BlockType();
        $blockType->setAdminName($config[0])
            ->setSlug($config[1])
            ->setCategory($config[2])
            ->setIconClass($this->getIconPath($config[3]))
            ->setDropdown(!empty($config[4]))
            ->setEditable(!isset($config[5]))
            ->setPosition($this->position)
            ->setCreatedBy($user);

        if (!empty($config[5])) {
            $blockType->setFieldType($config[5]);
        }

        if (!empty($config[6])) {
            $blockType->setRole($config[6]);
        }

        $this->position++;
        $this->manager->persist($blockType);
        $this->manager->flush();

        return $blockType;
    }

    /**
     * Get BlockTypes config
     *
     * @return array
     */
    private function getFormBocks(): array
    {
        return [
            [$this->translator->trans('Texte (form)', [], 'admin'), 'form-text', 'form', 'fal fa-text', false, Type\TextType::class],
            [$this->translator->trans('Zone de texte (form)', [], 'admin'), 'form-textarea', 'form', 'fal fa-comment-alt', false, Type\TextareaType::class],
            [$this->translator->trans('Sélecteur (form)', [], 'admin'), 'form-choice-type', 'form', 'fal fa-list-ul', false, Type\ChoiceType::class],
            [$this->translator->trans('Case à cocher (form)', [], 'admin'), 'form-checkbox', 'form', 'fal check-square', false, Type\CheckboxType::class],
            [$this->translator->trans('Email (form)', [], 'admin'), 'form-email', 'form', 'fal fa-at', false, Type\EmailType::class],
            [$this->translator->trans('Téléphone (form)', [], 'admin'), 'form-phone', 'form', 'fal fa-phone', false, Type\TelType::class],
            [$this->translator->trans('Code postal (form)', [], 'admin'), 'form-zip-code', 'form', 'fal fa-mailbox', false, Type\TextType::class],
            [$this->translator->trans('Date (form)', [], 'admin'), 'form-date', 'form', 'fal fa-calendar-alt', false, Type\DateType::class],
            [$this->translator->trans('Heure (form)', [], 'admin'), 'form-hour', 'form', 'fal fa-clock', false, Type\TimeType::class],
            [$this->translator->trans('Date & heure (form)', [], 'admin'), 'form-datetime', 'form', 'fal fa-calendar-star', false, Type\DateTimeType::class],
            [$this->translator->trans('Pièce jointe (form)', [], 'admin'), 'form-file', 'form', 'fal fa-file', false, Type\FileType::class],
            [$this->translator->trans('Groupe de mails (form)', [], 'admin'), 'form-emails', 'form', 'fal fa-users-class', false, Type\ChoiceType::class],
            [$this->translator->trans("Sélecteur d'entité (form)", [], 'admin'), 'form-choice-entity', 'form', 'fal fa-cubes', false, EntityType::class],
            [$this->translator->trans('Nombre (form)', [], 'admin'), 'form-integer', 'form', 'fal fa-sort-numeric-up-alt', false, Type\IntegerType::class],
            [$this->translator->trans('Pays (form)', [], 'admin'), 'form-country', 'form', 'fal fa-map-marked', false, Type\CountryType::class],
            [$this->translator->trans('Langues (form)', [], 'admin'), 'form-language', 'form', 'fal fa-flag', false, Type\LanguageType::class],
            [$this->translator->trans('URL (form)', [], 'admin'), 'form-url', 'form', 'fal fa-link', false, Type\UrlType::class],
            [$this->translator->trans('RGPD (form)', [], 'admin'), 'form-gdpr', 'form', 'fal fa-cookie', false, Type\ChoiceType::class],
            [$this->translator->trans('Caché (form)', [], 'admin'), 'form-hidden', 'form', 'fal fa-mask', false, Type\HiddenType::class],
            [$this->translator->trans('Bouton de soumission (form)', [], 'admin'), 'form-submit', 'form', 'fal fa-paper-plane', false, Type\SubmitType::class],
        ];
    }

    /**
     * Get BlockTypes config
     *
     * @return array
     */
    private function getLayoutBocks(): array
    {
        return [
            [$this->translator->trans('En-tête (layout)', [], 'admin'), 'layout-titleheader', 'layout', 'fal fa-text-width'],
            [$this->translator->trans('Titre (layout)', [], 'admin'), 'layout-title', 'layout', 'fal fa-text'],
            [$this->translator->trans('Texte (layout)', [], 'admin'), 'layout-body', 'layout', 'fal fa-paragraph'],
            [$this->translator->trans('Introduction (layout)', [], 'admin'), 'layout-intro', 'layout', 'fal fa-align-center'],
            [$this->translator->trans('Date (layout)', [], 'admin'), 'layout-date', 'layout', 'fal fa-calendar-alt'],
            [$this->translator->trans('Média (layout)', [], 'admin'), 'layout-image', 'layout', 'fal fa-image'],
            [$this->translator->trans('Galerie (layout)', [], 'admin'), 'layout-gallery', 'layout', 'fal fa-photo-video'],
            [$this->translator->trans('Carrousel (layout)', [], 'admin'), 'layout-slider', 'layout', 'fal fa-images'],
            [$this->translator->trans('Vidéo (layout)', [], 'admin'), 'layout-video', 'layout', 'fal fa-video'],
            [$this->translator->trans('Bouton de retour (layout)', [], 'admin'), 'layout-back-button', 'layout', 'fal fa-reply'],
            [$this->translator->trans('Lien (layout)', [], 'admin'), 'layout-link', 'layout', 'fal fa-link'],
            [$this->translator->trans('Boutons de partage (layout)', [], 'admin'), 'layout-share', 'layout', 'fal fa-share-alt'],
            [$this->translator->trans('Informations de contact (layout)', [], 'admin'), 'layout-contact', 'layout', 'fal fa-info'],
            [$this->translator->trans('Carte (layout custom)', [], 'admin'), 'layout-map', 'layout-map', 'fal fa-map-marked'],
            [$this->translator->trans('Tableaux des lots (layout catalog)', [], 'admin'), 'layout-catalog-lots-table', 'layout-catalog', 'fal fa-building'],
            [$this->translator->trans('Prestations (layout catalog)', [], 'admin'), 'layout-catalog-benefits', 'layout-catalog', 'fal fa-clipboard-list-check'],
        ];
    }

    /**
     * Get BlockTypes config
     *
     * @return array
     */
    private function getContentBlocks(): array
    {
        return [
            [$this->translator->trans('Action', [], 'admin'), 'core-action', 'core', 'fab fa-superpowers'],
            [$this->translator->trans('Titre', [], 'admin'), 'title', 'global', 'fal fa-text'],
            [$this->translator->trans('Texte', [], 'admin'), 'text', 'global', 'fal fa-paragraph'],
            [$this->translator->trans('Média', [], 'admin'), 'media', 'global', 'fal fa-image'],
            [$this->translator->trans('Lien', [], 'admin'), 'link', 'global', 'fal fa-link'],
            [$this->translator->trans('En-tête', [], 'admin'), 'titleheader', 'content', 'fal fa-text-width'],
            [$this->translator->trans('Vidéo', [], 'admin'), 'video', 'content', 'fal fa-video'],
            [$this->translator->trans('Mini fiche', [], 'admin'), 'card', 'content', 'fal fa-bookmark', true],
            [$this->translator->trans('Citation', [], 'admin'), 'blockquote', 'content', 'fal fa-quote-right', true],
            [$this->translator->trans('Collapse', [], 'admin'), 'collapse', 'content', 'fal fa-line-height', true],
            [$this->translator->trans('Pop-up', [], 'admin'), 'modal', 'content', 'fal fa-comment-alt', true],
            [$this->translator->trans('Alerte', [], 'admin'), 'alert', 'global', 'fal fa-exclamation-triangle', true],
            [$this->translator->trans('Icône', [], 'admin'), 'icon', 'global', 'fab fa-ravelry', true],
            [$this->translator->trans('Module', [], 'admin'), 'action', 'action', 'fal fa-star', true],
            [$this->translator->trans('Séparateur', [], 'admin'), 'separator', 'global', 'fal fa-grip-lines', true],
            [$this->translator->trans('Widget', [], 'admin'), 'widget', 'content', 'fal fa-code', true],
            [$this->translator->trans('Boutons de partages', [], 'admin'), 'share', 'global', 'fal fa-share-alt', true, true],
            [$this->translator->trans('Compteur', [], 'admin'), 'counter', 'global', 'fal fa-sort-numeric-up-alt', true]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            SecurityFixture::class
        ];
    }
}