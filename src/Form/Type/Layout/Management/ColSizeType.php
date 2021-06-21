<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Col;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColSizeType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColSizeType extends AbstractType
{
    private $translator;

    /**
     * ColSizeType constructor.
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
        $limit = 12;
        for ($i = 1; $i <= $limit; $i++) {
            $choices[$i] = $i;
        }

        $builder->add('size', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Choisissez une taille', [], 'admin'),
            'required' => true,
            'choices' => $choices,
            'display' => 'classic',
            "expanded" => true
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Ajouter', [], 'admin'),
            'attr' => [
                'class' => 'btn-info d-none edit-element-submit-btn btn-lg disable-preloader'
            ]
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