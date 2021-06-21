<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SeparatorType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeparatorType extends AbstractType
{
    private $translator;

    /**
     * SeparatorType constructor.
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
        $builder->add('template', WidgetType\TemplateBlockType::class);

        $builder->add('height', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans('Hauteur du séparateur (px)', [], 'admin'),
            'attr' => [
                'group' => 'col-md-6',
                'placeholder' => $this->translator->trans('Saisissez une hauteur', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('color', WidgetType\BackgroundColorSelectType::class, [
            'label' => $this->translator->trans('Couleur de fond', [], 'admin'),
            'expanded' => false,
            'attr' => [
                'class' => 'select-icons',
                'group' => 'col-md-3'
            ]
        ]);

        $builder->add('hideMobile', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Cacher le séparateur en mobile ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3 my-md-auto']
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_back' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}