<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\FieldValue;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FieldValueType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldValueType extends AbstractType
{
    private $translator;

    /**
     * FieldValueType constructor.
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
        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        $builder->add('i18ns', CollectionType::class, [
            'label' => false,
            'entry_type' => FieldValueI18nType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FieldValue::class,
            'website' => NULL,
            'field_type' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}