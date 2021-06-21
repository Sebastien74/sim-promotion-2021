<?php

namespace App\Form\Type\Seo;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AnalyticsDateType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AnalyticsDateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', BirthdayType::class, [
            'widget' => 'single_text',
            'format' => $options['format'],
            'html5' => false,
            'attr' => ['class' => 'js-datepicker']
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
            'format' => NULL,
            'csrf_protection' => false,
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return NULL;
    }
}