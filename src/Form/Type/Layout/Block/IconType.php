<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Form\Type\Layout\Management\AlignmentType;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * IconType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconType extends AbstractType
{
    private $translator;

    /**
     * IconType constructor.
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

        $builder->add('icon', WidgetType\IconType::class, [
            'required' => true,
            'attr' => ['class' => 'select-icons', 'group' => 'col-md-4'],
        ]);

        $builder->add('color', WidgetType\AppColorType::class, [
            'label' => $this->translator->trans("Couleur de l'icône", [], 'admin'),
            'attr' => ['class' => 'select-icons', 'group' => 'col-md-4']
        ]);

        $builder->add('iconSize', ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans("Taille de l'icône", [], 'admin'),
            'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
            'attr' => ['group' => 'col-md-4'],
            'choices' => ['XS' => 'xs', 'S' => 'sm', 'M' => 'md', 'L' => 'lg', 'XL' => 'xl', 'XXL' => 'xxl']
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