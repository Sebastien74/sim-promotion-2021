<?php

namespace App\Form\Type\Information;

use App\Entity\Information\Phone;
use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddressPhoneType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressPhoneType extends AbstractType
{
    private $translator;

    /**
     * AddressPhoneType constructor.
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
        $builder->add('number', Type\TextType::class, [
            'label' => $this->translator->trans('Numéro de téléphone', [], 'admin'),
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un numéro', [], 'admin'),
                'group' => 'col-md-4'
            ],
            'constraints' => [new Validator\Phone()]
        ]);

        $builder->add('tagNumber', Type\TextType::class, [
            'label' => $this->translator->trans('Numéro de téléphone (href)', [], 'admin'),
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un numéro', [], 'admin'),
                'group' => 'col-md-4'
            ],
            'constraints' => [new Validator\Phone()]
        ]);

        $builder->add('type', Type\ChoiceType::class, [
            'label' => $this->translator->trans("Type", [], 'admin'),
            'display' => 'search',
            'choices' => [
                $this->translator->trans('Fixe', [], 'admin') => 'fixe',
                $this->translator->trans('Portable', [], 'admin') => 'mobile',
                $this->translator->trans('Fax', [], 'admin') => 'fax'
            ],
            'attr' => [
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => 'col-md-4'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}