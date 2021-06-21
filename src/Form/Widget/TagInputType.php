<?php

namespace App\Form\Widget;

use App\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TagInputType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TagInputType extends AbstractType
{
    private $translator;

    /**
     * TagInputType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ArrayToStringTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'role' => 'tagsinput',
            'label_attr' => [
                'class' => 'w-100'
            ],
            'attr' => [
                'placeholder' => $this->translator->trans('Ajoutez', [], 'admin')
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return TextType::class;
    }
}