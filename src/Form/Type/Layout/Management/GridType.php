<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Grid;
use App\Form\Widget as WidgetType;
use App\Form\Widget\AdminNameType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GridType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridType extends AbstractType
{
    private $translator;

    /**
     * GridType constructor.
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

        $adminName = new AdminNameType($this->translator);
        $adminName->add($builder);

        if (!$isNew) {
            $builder->add('cols', CollectionType::class, [
                'label' => false,
                'entry_type' => GridColType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => ['class' => 'grid-col'],
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
            'data_class' => Grid::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}