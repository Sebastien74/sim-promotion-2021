<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ChoiceExtension
 *
 * Extends ChoiceType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ChoiceExtension extends AbstractTypeExtension
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
        $noSelect2Fields = ['day', 'month', 'year', 'hour', 'minute'];
        $fieldName = $view->vars['name'];
        $setDisplay = $options['display'] === 'search' ? 'select-2' : $options['display'];
        $display = !in_array($fieldName, $noSelect2Fields) ? $setDisplay : 'classic';

        $view->vars['attr']['class'] = !empty($options['attr']['class'])
            ? $options['attr']['class'] . ' ' . $display
            : $display;

        $view->vars['attr']['class'] = !empty($options['attr']['class'])
            ? $options['attr']['class'] . ' ' . $display
            : $display;

        if (!empty($view->vars['attr']['group'])) {
            $view->vars['attr']['class'] = $view->vars['attr']['class'] . ' ' . $view->vars['attr']['group'];
        }

        $view->vars['attr']['data-dropdown-class'] = $options['dropdown_class'] != 'select-dropdown-container'
            ? $options['dropdown_class']
            : ($options['multiple'] ? $options['dropdown_class'] . '-multiple' : $options['dropdown_class'] . '-single');

        $view->vars['label_attr']['class'] = !empty($options['label_class']['class'])
            ? $options['attr']['class'] . ' ' . $options['label_class']
            : $options['label_class'];

        $view->vars['customized_options'] = $options['customized_options'];
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
            'customized_options' => [],
            'display' => '',
            'dropdown_class' => 'select-dropdown-container',
            'label_class' => ''
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }
}