<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\CssClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CssClassType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CssClassType extends AbstractType
{
    private $translator;

    /**
     * CssClassType constructor.
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
        $builder->add('name', Type\TextType::class, [
            'label' => $this->translator->trans('Nom', [], 'admin'),
            'attr' => [
                'group' => 'col-md-4',
                'placeholder' => $this->translator->trans('Saisissez une classe', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('description', Type\TextType::class, [
            'label' => $this->translator->trans('Description', [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-8',
                'placeholder' => $this->translator->trans('Saisissez une description', [], 'admin')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CssClass::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}