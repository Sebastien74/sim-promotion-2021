<?php

namespace App\Form\Type\Core\Init;

use App\Form\Widget\FontsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * StylesType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StylesType extends AbstractType
{
    private $translator;

    /**
     * StylesType constructor.
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
        $this->getColors($builder);

        $builder->add('fonts', FontsType::class, [
            'required' => false,
            'multiple' => true,
            'label' => $this->translator->trans("Police", [], 'core_init')
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans("Enregistrer", [], 'core_init'),
            'attr' => ['class' => 'btn-primary mt-4']
        ]);
    }

    /**
     * Get Networks
     *
     * @param FormBuilderInterface $builder
     */
    private function getColors(FormBuilderInterface $builder)
    {
        $colors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'danger_light', 'light'];
        foreach ($colors as $color) {
            $builder->add('color_' . $color, Type\TextType::class, [
                'required' => false,
                'display' => 'floating',
                'label' => ucfirst(str_replace('_', ' ', $color)),
                'attr' => ['group' => 'col-md-3', 'data-category' => 'colors']
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "project_init";
    }
}