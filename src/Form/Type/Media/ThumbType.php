<?php

namespace App\Form\Type\Media;

use App\Entity\Media\Thumb;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ThumbType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbType extends AbstractType
{
    private $translator;

    /**
     * ThumbType constructor.
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
        /** @var Thumb $thumb */
        $thumb = $builder->getData();
        $configuration = $thumb->getConfiguration();
        $isInfinite = $configuration->getWidth() < 1 && $configuration->getHeight() < 1;
        $sizeFieldType = $isInfinite ? Type\IntegerType::class : Type\HiddenType::class;

        $builder->add('dataX', Type\HiddenType::class, [
            'attr' => ['class' => 'dataX'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('dataY', Type\HiddenType::class, [
            'attr' => ['class' => 'dataY'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('width', $sizeFieldType, [
            'label' => $isInfinite ? $this->translator->trans('Largeur', [], 'admin') : false,
            'attr' => ['class' => 'dataWidth', 'group' => 'col-md-6 mb-0'],
            'constraints' => [ new Assert\NotBlank() ],
        ]);

        $builder->add('height', $sizeFieldType, [
            'label' => $isInfinite ? $this->translator->trans('Hauteur', [], 'admin') : false,
            'attr' => ['class' => 'dataHeight', 'group' => 'col-md-6 mb-0'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('rotate', Type\HiddenType::class, [
            'attr' => ['class' => 'dataRotate'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('scaleX', Type\HiddenType::class, [
            'attr' => ['class' => 'dataScaleX'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('scaleY', Type\HiddenType::class, [
            'attr' => ['class' => 'dataScaleY'],
            'constraints' => [ new Assert\NotBlank() ]
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => [ 'class' => 'btn-info' ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Thumb::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}