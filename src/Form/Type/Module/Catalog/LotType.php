<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Module\Catalog\Lot;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LotType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LotType extends AbstractType
{
    private $translator;

    /**
     * LotType constructor.
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
        $builder->add('reference', Type\TextType::class, [
            'label' => $this->translator->trans("Lot N°", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une référence", [], 'admin'),
                'group' => "col-md-2",
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('type', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Type", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une type", [], 'admin'),
                'group' => "col-md-2",
            ]
        ]);

        $builder->add('surface', Type\NumberType::class, [
            'required' => false,
            'label' => $this->translator->trans("Surface", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une surface", [], 'admin'),
                'group' => "col-md-2",
            ]
        ]);

        $builder->add('balconySurface', Type\NumberType::class, [
            'required' => false,
            'label' => $this->translator->trans("Surface (Balcon/Terrasse)", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une surface", [], 'admin'),
                'group' => "col-md-2",
            ]
        ]);

        $builder->add('price', Type\NumberType::class, [
            'required' => false,
            'label' => $this->translator->trans("Prix", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un prix", [], 'admin'),
                'group' => "col-md-2",
            ]
        ]);

        $builder->add('sold', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Vendu', [], 'admin'),
            'attr' => ['group' => 'col-md-2 d-flex align-items-end', 'class' => 'w-100']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lot::class,
            'website' => NULL,
            'prototypePosition' => true,
            'translation_domain' => 'admin'
        ]);
    }
}