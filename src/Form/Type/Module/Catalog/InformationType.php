<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Information\Information;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

        $builder->add('addresses', CollectionType::class, [
            'label' => false,
            'entry_type' => AddressType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'address',
                    'group' => 'col-md-12',
                    'disableTitle' => true,
                    'caption' => $this->translator->trans('Adresses', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter une adresse', [], 'admin')
                ],
                'website' => $website
            ]
        ]);
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