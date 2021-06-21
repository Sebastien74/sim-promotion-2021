<?php

namespace App\Form\Type\Media;

use App\Entity\Media\ThumbConfiguration;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ThumbConfigurationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbConfigurationType extends AbstractType
{
    private $translator;

    /**
     * ThumbConfigurationType constructor.
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

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => 'col-md-6']);

        $builder->add('width', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans('Largeur (px)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une largeur', [], 'admin'),
                'group' => 'col-md-3'
            ]
        ]);

        $builder->add('height', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans('Hauteur (px)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une hauteur', [], 'admin'),
                'group' => 'col-md-3'
            ]
        ]);

        if (!$isNew) {

            $builder->add('actions', Type\CollectionType::class, [
                'required' => false,
                'label' => false,
                'entry_type' => ThumbActionType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'configuration',
                    ],
                    'website' => $options['website']
                ]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ThumbConfiguration::class,
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}