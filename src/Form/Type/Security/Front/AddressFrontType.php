<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Information\Address;
use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddressFrontType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressFrontType extends AbstractType
{
    private $translator;

    /**
     * AddressFrontType constructor.
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
        $builder->add('address', Type\TextType::class, [
            'label' => $this->translator->trans("Adresse", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-9',
                'placeholder' => $this->translator->trans('Saisissez une adresse', [], 'admin')
            ]
        ]);

        $builder->add('zipCode', Type\TextType::class, [
            'label' => $this->translator->trans("Code postal", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez un code postal', [], 'admin')
            ],
            'constraints' => [new Validator\ZipCode()]
        ]);

        $builder->add('city', Type\TextType::class, [
            'label' => $this->translator->trans("Ville", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une ville', [], 'admin')
            ]
        ]);

        $builder->add('department', Type\TextType::class, [
            'label' => $this->translator->trans("Département", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une département', [], 'admin')
            ]
        ]);

        $builder->add('region', Type\TextType::class, [
            'label' => $this->translator->trans("Région", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans('Saisissez une région', [], 'admin')
            ]
        ]);

        $builder->add('country', Type\CountryType::class, [
            'label' => $this->translator->trans("Pays", [], 'admin'),
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Sélectionnez un pays', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'website' => NULL,
            'translation_domain' => 'front'
        ]);
    }
}