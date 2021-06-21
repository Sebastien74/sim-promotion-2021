<?php

namespace App\Form\Type\Information;

use App\Entity\Core\Configuration;
use App\Entity\Information\Address;
use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddressType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressType extends AbstractType
{
    private $translator;

    /**
     * AddressType constructor.
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
        /** @var Configuration $configuration */
        $configuration = $options['website']->getConfiguration();
        $locales = $this->getLocales($configuration);
        $multiLocales = count($locales) > 1;

        $builder->add('name', Type\TextType::class, [
            'label' => $this->translator->trans("Raison sociale", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => $multiLocales ? 'col-md-9' : 'col-12',
                'placeholder' => $this->translator->trans('Saisissez une raison sociale', [], 'admin')
            ]
        ]);

        if ($multiLocales) {

            $builder->add('locale', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Langue', [], 'admin'),
                'choices' => $locales,
                'choice_translation_domain' => false,
                'attr' => ['class' => 'select-icons', 'group' => 'col-md-3'],
                'choice_attr' => function ($iso, $key, $value) {
                    return [
                        'data-image' => '/medias/icons/flags/' . strtolower($iso) . '.svg',
                        'data-class' => 'flag mt-min',
                        'data-text' => true,
                        'data-height' => 14,
                        'data-width' => 19,
                    ];
                },
                'constraints' => [new Assert\NotBlank()]
            ]);
        } else {
            $builder->add('locale', Type\HiddenType::class, [
                'data' => $configuration->getLocale()
            ]);
        }

        $builder->add('zones', Type\ChoiceType::class, [
            'label' => $this->translator->trans("Zones d'affichage", [], 'admin'),
            'display' => 'search',
            'multiple' => true,
            'required' => false,
            'choices' => [
                $this->translator->trans('Page de contact', [], 'admin') => 'contact',
                $this->translator->trans('Navigation', [], 'admin') => 'header',
                $this->translator->trans('Pied de page', [], 'admin') => 'footer',
                $this->translator->trans('E-mail', [], 'admin') => 'email',
                $this->translator->trans('Page de maintenance', [], 'admin') => 'maintenance'
            ],
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez une zone', [], 'admin')
            ]
        ]);

        $builder->add('address', Type\TextType::class, [
            'label' => $this->translator->trans("Adresse", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-9',
                'placeholder' => $this->translator->trans('Saisissez une adresse', [], 'admin')
            ]
        ]);

        $builder->add('zipCode', Type\TextType::class, [
            'label' => $this->translator->trans("Code postal", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez un code postal', [], 'admin')
            ],
            'constraints' => [new Validator\ZipCode()]
        ]);

        $builder->add('city', Type\TextType::class, [
            'label' => $this->translator->trans("Ville", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une ville', [], 'admin')
            ]
        ]);

        $builder->add('department', Type\TextType::class, [
            'label' => $this->translator->trans("Département", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une département', [], 'admin')
            ]
        ]);

        $builder->add('region', Type\TextType::class, [
            'label' => $this->translator->trans("Région", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une région', [], 'admin')
            ]
        ]);

        $builder->add('country', Type\CountryType::class, [
            'label' => $this->translator->trans("Pays", [], 'admin'),
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Sélectionnez un pays', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);

        $builder->add('phones', CollectionType::class, [
            'label' => false,
            'entry_type' => AddressPhoneType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'address-phone',
                    'icon' => 'fal fa-phone',
                    'caption' => $this->translator->trans('Numéro de téléphone', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter un numéro', [], 'admin')
                ],
                'website' => $options['website']
            ]
        ]);

        $builder->add('emails', CollectionType::class, [
            'label' => false,
            'entry_type' => AddressEmailType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'group' => 'col-md-3',
                    'class' => 'address-email',
                    'icon' => 'fal fa-at',
                    'caption' => $this->translator->trans('E-mails', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter un e-mail', [], 'admin')
                ],
                'website' => $options['website']
            ]
        ]);

        $builder->add('schedule', Type\TextareaType::class, [
            'required' => false,
            'label' => $this->translator->trans('Horaires', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez vos horaires', [], 'admin')
            ]
        ]);
    }

    /**
     * Get Website locales
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getLocales(Configuration $configuration): array
    {
        $defaultLocale = $configuration->getLocale();
        $locales[Languages::getName($defaultLocale)] = $defaultLocale;
        foreach ($configuration->getLocales() as $locale) {
            $locales[Languages::getName($locale)] = $locale;
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'website' => NULL,
            'prototypePosition' => true,
            'translation_domain' => 'admin'
        ]);
    }
}