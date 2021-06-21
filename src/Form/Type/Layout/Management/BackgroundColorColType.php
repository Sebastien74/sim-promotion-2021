<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Col;
use App\Form\Widget\BackgroundColorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * BackgroundColorColType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BackgroundColorColType extends AbstractType
{
    private $translator;

    /**
     * BackgroundColorColType constructor.
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
        $builder->add('backgroundColor', BackgroundColorType::class, [
            'label' => false
        ]);

        $builder->add('backgroundFullSize', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Toute la largeur ?', [], 'admin'),
            'attr' => ['group' => 'input-group-background-full-size col-12 text-center mt-3']
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Enregistrer', [], 'admin'),
            'attr' => ['class' => 'btn-info edit-element-submit-btn']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Col::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}