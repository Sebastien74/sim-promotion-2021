<?php

namespace App\Form\Type\Translation;

use App\Entity\Translation\TranslationUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UnitType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UnitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translations', CollectionType::class, [
            'label' => false,
            'entry_type' => TranslationType::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationUnit::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}