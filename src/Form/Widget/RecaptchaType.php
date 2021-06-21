<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RecaptchaType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RecaptchaType extends AbstractType
{
    private $translator;

    /**
     * RecaptchaType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add fields
     *
     * @param FormBuilderInterface $builder
     * @param mixed $entity
     */
    public function add(FormBuilderInterface $builder, $entity)
    {
        $entity = method_exists($entity, 'getConfiguration') ? $entity->getConfiguration() : $entity;

        if (method_exists($entity, 'getRecaptcha') && $entity->getRecaptcha()) {

            $builder->add('field_ho', Type\TextType::class, [
                'mapped' => false,
                'label' => $this->translator->trans("Valeur"),
                'required' => true,
                'label_attr' => ['class' => 'd-none'],
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez une valeur", [], 'front_form'),
                    'class' => 'form-field-none field_ho',
                    'autocomplete' => 'off'
                ]
            ]);

            $builder->add('field_ho_entitled', Type\TextType::class, [
                'mapped' => false,
                'label' => $this->translator->trans("Intitulé"),
                'label_attr' => ['class' => 'd-none'],
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un intitulé", [], 'front_form'),
                    'class' => 'form-field-none',
                    'autocomplete' => 'off'
                ]
            ]);
        }
    }
}