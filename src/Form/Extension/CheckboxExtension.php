<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CheckboxExtension
 *
 * Extends CheckboxType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CheckboxExtension extends AbstractTypeExtension
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
        if ($options['display'] === "checkbox-custom") {
            $view->vars['label_attr']['class'] = $options['display'] . " cursor";
        } elseif ($options['display'] === "switch") {
            $view->vars['label_attr']['class'] = "custom-control-label cursor";
        } elseif ($options['display'] === "button") {
            $view->vars['label_attr']['class'] = "button";
            $view->vars['attr']['data-color'] = $options['color'];
        }

        $parentConfiguration = $form->getParent()->getConfig();
        $choiceList = !empty($form->getParent()->getConfig()->getAttributes()['choice_list']) ? $form->getParent()->getConfig()->getAttributes()['choice_list'] : NULL;
        if ($parentConfiguration->getRequired() && $choiceList && count($choiceList->getChoices()) === 1) {
            $view->vars['required'] = true;
        }

        if ($options['uniq_id']) {
            $view->vars['id'] = $form->getName() . '-' . uniqid();
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
            'display' => 'checkbox-custom',
            'color' => 'primary',
            'uniq_id' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [CheckboxType::class];
    }
}