<?php

namespace App\Form\Type\Core;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TreeType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TreeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nestable_output', null, [
            'label' => false,
            'required' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL,
            'old_position' => NULL,
            'iterations' => 0,
            'translation_domain' => 'admin'
        ]);
    }
}