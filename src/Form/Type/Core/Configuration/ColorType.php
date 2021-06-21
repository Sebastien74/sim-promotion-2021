<?php

namespace App\Form\Type\Core\Configuration;

use App\Entity\Core\Color;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColorType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColorType extends AbstractType
{
    private $translator;

    /**
     * ColorType constructor.
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
        $builder->add('adminName', Type\TextType::class, [
            'label' => $this->translator->trans('Nom de la couleur', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un nom', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('color', Type\TextType::class, [
            'label' => $this->translator->trans('Couleur', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une couleur', [], 'admin'),
                'class' => 'colorpicker'
            ]
        ]);

        $builder->add('slug', Type\TextType::class, [
            'label' => $this->translator->trans('Code CSS', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un code', [], 'admin')
            ]
        ]);

        $builder->add('category', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Catégorie', [], 'admin'),
            'display' => 'search',
            'choices' => [
                $this->translator->trans('Couleur de fond', [], 'admin') => 'background',
                $this->translator->trans('Bouton', [], 'admin') => 'button',
                $this->translator->trans('Couleur', [], 'admin') => 'color',
                $this->translator->trans('Favicon', [], 'admin') => 'favicon',
                $this->translator->trans('Alerte', [], 'admin') => 'alert',
            ],
            'attr' => [
                'placeholder' => $this->translator->trans('Séléctionnez', [], 'admin')
            ]
        ]);

        $builder->add('isActive', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Actif', [], 'admin'),
            'attr' => ['class' => 'w-100']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Color::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}