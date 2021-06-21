<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TextExtension
 *
 * Extends TextType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TextExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!empty($options['counter'])) {
            $view->vars['counter'] = $options['counter'];
        }

        if (!empty($options['bytes'])) {
            $view->vars['bytes'] = $options['bytes'];
        }

        if (!empty($options['display'])) {
            $view->vars['display'] = $options['display'];
        }

        if (!empty($options['role'])) {
            $view->vars['attr']['data-role'] = $options['role'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'counter' => NULL,
            'bytes' => NULL,
            'display' => NULL,
            'role' => NULL
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }
}