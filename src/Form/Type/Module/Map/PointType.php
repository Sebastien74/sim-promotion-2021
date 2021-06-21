<?php

namespace App\Form\Type\Module\Map;

use App\Entity\Module\Map\Category;
use App\Entity\Module\Map\Point;
use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Form\Type\Information\PhoneType;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PointType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PointType extends AbstractType
{
    private $translator;
    private $entityManager;

    /**
     * TabType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();
        $website = $options['website'];

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-10'
        ]);

        if (!$isNew) {

            $builder->add('marker', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Marqueur', [], 'admin'),
                'choices' => $this->getMarkers($options['website']),
                'choice_attr' => function ($dir, $key, $value) {
                    return ['data-background' => strtolower($dir)];
                },
                'attr' => [
                    'group' => 'col-md-2 markers-select',
                    'class' => 'select-icons'
                ]
            ]);

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans("Catégories", [], 'admin'),
                'required' => false,
                'display' => 'search',
                'attr' => [
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'class' => Category::class,
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true
            ]);

            $builder->add('address', AddressType::class, ['label' => false]);

            $builder->add('phones', CollectionType::class, [
                'label' => false,
                'entry_type' => PhoneType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'phone',
                        'icon' => 'fal fa-phone',
                        'group' => 'col-md-3',
                        'caption' => $this->translator->trans('Numéro de téléphone', [], 'admin'),
                        'button' => $this->translator->trans('Ajouter un numéro', [], 'admin')
                    ],
                    'locale' => false,
                    'entitled' => false,
                    'type' => false,
                    'zones' => false,
                    'website' => $website
                ]
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'content_config' => false,
                'data_config' => true,
                'title_force' => true,
                'excludes_fields' => ['targetAlignment', 'targetStyle', 'headerTable'],
                'fields' => ['title', 'body', 'targetLink' => 'col-md-12', 'targetPage' => 'col-md-6', 'targetLabel' => 'col-md-6']
            ]);

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, [
                'entry_options' => ['onlyMedia' => true],
                'data_config' => true
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get markers choices
     *
     * @param Website $website
     * @return array
     */
    private function getMarkers(Website $website)
    {
        $mapFolder = $this->entityManager->getRepository(Folder::class)->findOneBy([
            'website' => $website,
            'slug' => 'map'
        ]);

        $markers = [];
        foreach ($mapFolder->getMedias() as $media) {
            $markers[$media->getFilename()] = '/uploads/' . $website->getUploadDirname() . '/' . $media->getFilename();
        }

        return $markers;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Point::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}