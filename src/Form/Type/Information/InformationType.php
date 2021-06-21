<?php

namespace App\Form\Type\Information;

use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InformationType
 *
 * @property TranslatorInterface $translator
 * @property AuthorizationCheckerInterface $authorizationChecker
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationType extends AbstractType
{
    private $translator;
    private $authorizationChecker;

    /**
     * InformationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Website $website */
        $website = $options['website'];
        $multiLocales = count($website->getConfiguration()->getAllLocales()) > 1;

        $i18nsFields = ['title', 'introduction' => 'col-12 editor', 'body'];
        if ($this->authorizationChecker->isGranted('ROLE_ALERT')) {
            $i18nsFields['placeholder'] = 'col-12 editor';
            $i18nsFields['active'] = 'col-12';
        }

        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $website,
            'content_config' => false,
            'fields' => $i18nsFields,
            'excludes_fields' => ['headerTable'],
            'fields_type' => ['placeholder' => TextareaType::class],
            'label_fields' => [
                'title' => $this->translator->trans('Raison sociale', [], 'admin'),
                'introduction' => $this->translator->trans('Description pied de page', [], 'admin'),
                'body' => $this->translator->trans('Horaires', [], 'admin'),
                'placeholder' => $this->translator->trans("Message d'alerte", [], 'admin'),
                'active' => $this->translator->trans("Activer le message d'alerte ?", [], 'admin'),
            ],
            'placeholder_fields' => [
                'title' => $this->translator->trans('Saisissez une raison sociale', [], 'admin'),
                'introduction' => $this->translator->trans('Saisissez une description', [], 'admin'),
                'body' => $this->translator->trans('Saisissez vos horaires', [], 'admin'),
                'placeholder' => $this->translator->trans('Saisissez une alerte', [], 'admin')
            ],
        ]);

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
                'website' => $website
            ]
        ]);

        $builder->add('emails', CollectionType::class, [
            'label' => false,
            'entry_type' => EmailType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'email',
                    'icon' => 'fal fa-at',
                    'group' => 'col-md-3',
                    'caption' => $this->translator->trans('E-mails', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter un e-mail', [], 'admin')
                ],
                'website' => $website
            ]
        ]);

        $builder->add('addresses', CollectionType::class, [
            'label' => false,
            'entry_type' => AddressType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'address',
                    'icon' => 'fal fa-map-marked-alt',
                    'group' => 'col-md-12',
                    'caption' => $this->translator->trans('Adresses', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter une adresse', [], 'admin')
                ],
                'website' => $website
            ]
        ]);

        $builder->add('legals', CollectionType::class, [
            'label' => false,
            'entry_type' => LegalType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'legals',
                    'icon' => 'fal fa-balance-scale-left',
                    'group' => 'col-12',
                    'deletable' => $multiLocales,
                    'caption' => $this->translator->trans('Mentions légales', [], 'admin'),
                    'button' => $multiLocales ? $this->translator->trans('Ajouter des informations', [], 'admin') : false
                ],
                'website' => $website
            ]
        ]);

        $builder->add('socialNetworks', CollectionType::class, [
            'label' => false,
            'entry_type' => SocialNetworkType::class
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Information::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}