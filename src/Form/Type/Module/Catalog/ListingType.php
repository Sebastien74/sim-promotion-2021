<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Category;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Module\Catalog\Listing;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ListingType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingType extends AbstractType
{
    private $translator;
    private $website;

    /**
     * ListingType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();
        $this->website = $options['website'];
        $selectChoices = $this->getSelectChoices();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-3'
        ]);

        if (!$isNew) {

            $builder->add('orderBy', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Ordonner par', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('Position', [], 'admin') => 'position',
                    $this->translator->trans('Titre', [], 'admin') => 'title',
                    $this->translator->trans('Date de création', [], 'admin') => 'createdAt',
                    $this->translator->trans('Date de début de publication', [], 'admin') => 'publicationStart'
                ],
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('orderSort', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Trier par ordre', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('Croissant', [], 'admin') => 'ASC',
                    $this->translator->trans('Décroissant', [], 'admin') => 'DESC'
                ],
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('display', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Affichage des filtres', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('Configuration des types de filtres)', [], 'admin') => 'configuration',
                    $this->translator->trans('Tout', [], 'admin') => 'all',
                    $this->translator->trans('Désactiver', [], 'admin') => 'disable',
                ],
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('searchText', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Activer la recherche par mots-clés', [], 'admin'),
                'attr' => ['group' => 'col-md-3 d-flex align-items-end', 'class' => 'w-100']
            ]);

            $builder->add('catalogs', EntityType::class, [
                'label' => $this->translator->trans("Filtres des produits par catalogues", [], 'admin'),
                'required' => false,
                'class' => Catalog::class,
                'attr' => [
                    'group' => 'col-md-9',
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'display' => "search"
            ]);

            $builder->add('searchCatalogs', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Type de filtre (Catalogues)', [], 'admin'),
                'display' => 'search',
                'choices' => $selectChoices,
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans("Filtres des produits par catégories", [], 'admin'),
                'required' => false,
                'class' => Category::class,
                'attr' => [
                    'group' => 'col-md-9',
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'display' => "search"
            ]);

            $builder->add('searchCategories', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Type de filtre (Catégories)', [], 'admin'),
                'display' => 'search',
                'choices' => $selectChoices,
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('features', EntityType::class, [
                'label' => $this->translator->trans("Filtres des produits par caractéristiques", [], 'admin'),
                'required' => false,
                'class' => Feature::class,
                'attr' => [
                    'group' => 'col-md-9',
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'display' => "search"
            ]);

            $builder->add('searchFeatures', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Type de filtre (Caractéristiques)', [], 'admin'),
                'display' => 'search',
                'choices' => $selectChoices,
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('counter', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Activer le compteur de résultats', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('featuresValues', CollectionType::class, [
                'label' => false,
                'entry_type' => ListingFeatureValueType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'block_name' => 'values',
                'entry_options' => [
                    'attr' => [
                        'class' => 'feature',
                        'icon' => 'fal fa-filter',
                        'group' => 'col-md-4',
                        'caption' => $this->translator->trans('Filtres des produits par valeurs', [], 'admin'),
                        'button' => $this->translator->trans('Ajouter une valeur', [], 'admin')
                    ],
                    'website' => $options['website']
                ]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get select choices
     *
     * @return array
     */
    private function getSelectChoices(): array
    {
        return [
            $this->translator->trans('Ne pas afficher les filtres', [], 'admin') => NULL,
            $this->translator->trans('Sélécteur choix unique', [], 'admin') => 'select-uniq',
            $this->translator->trans('Sélécteur choix multiple', [], 'admin') => 'select-multiple',
            $this->translator->trans('Radios boutons', [], 'admin') => 'radios',
            $this->translator->trans('Cases à cocher', [], 'admin') => 'checkboxes',
            $this->translator->trans('Liens', [], 'admin') => 'links',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Listing::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}