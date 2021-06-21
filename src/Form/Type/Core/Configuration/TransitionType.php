<?php

namespace App\Form\Type\Core\Configuration;

use App\Entity\Core\Transition;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TransitionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TransitionType extends AbstractType
{
    private $translator;

    /**
     * TransitionType constructor.
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
        $adminName->add($builder, [
            'slug' => true,
            'adminNameGroup' => 'col-12',
            'slugGroup' => 'col-md-6'
        ]);

        $builder->add('section', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Section", [], 'admin'),
            'attr' => ['group' => 'col-md-6', 'placeholder' => $this->translator->trans('Saisissez une section', [], 'admin')]
        ]);

        $builder->add('aosEffect', WidgetType\AosEffectType::class,
            ['attr' => ['data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')]
            ]);

        $builder->add('laxPreset', WidgetType\LaxEffectType::class,
            ['attr' => ['data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')]
            ]);

        $builder->add('parameters', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Personnalisation", [], 'admin'),
            'attr' => ['placeholder' => $this->translator->trans('Saisissez vos valeurs', [], 'admin')]
        ]);

        $builder->add('duration', WidgetType\EffectDurationType::class,
            ['attr' => ['group' => 'col-md-4', 'placeholder' => $this->translator->trans('Saisissez une durée', [], 'admin')]
        ]);

        $builder->add('delay', WidgetType\EffectDelayType::class,
            ['attr' => ['group' => 'col-md-4', 'placeholder' => $this->translator->trans('Saisissez un délai', [], 'admin')]
        ]);

        $builder->add('offset', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Offset", [], 'admin'),
            'attr' => ['group' => 'col-md-4', 'placeholder' => $this->translator->trans('Saisissez un offset', [], 'admin')]
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
            'data_class' => Transition::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}