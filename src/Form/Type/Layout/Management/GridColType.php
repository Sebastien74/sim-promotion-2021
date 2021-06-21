<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\GridCol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GridColType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridColType extends AbstractType
{
    private $translator;

    /**
     * GridColType constructor.
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
        $builder->add('position', Type\IntegerType::class, [
            'label' => $this->translator->trans("Position", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une position", [], 'admin'),
                'group' => 'col-md-6'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $choices = [];
        for ($i = 1; $i <= 12; $i++) {
            $choices[$i] = $i;
        }
        $builder->add('size', Type\ChoiceType::class, [
            'label' => $this->translator->trans("Taille", [], 'admin'),
            'display' => 'search',
            'attr' => ['group' => 'col-md-6'],
            'choices' => $choices,
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GridCol::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}