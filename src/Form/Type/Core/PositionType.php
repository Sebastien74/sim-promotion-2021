<?php

namespace App\Form\Type\Core;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PositionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PositionType extends AbstractType
{
    private $translator;

    /**
     * ConfigurationType constructor.
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
        $choices = [];
        for ($x = 1; $x <= $options['iterations']; $x++) {
            $choices[$x] = $x;
        }

        $builder->add('position', Type\ChoiceType::class, [
            'label' => false,
            'display' => 'search',
            'choices' => $choices
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Enregistrer', [], 'admin'),
            'attr' => ['class' => 'btn-info']
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