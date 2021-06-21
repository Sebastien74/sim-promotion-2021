<?php

namespace App\Form\Type\Module\Map;

use App\Entity\Module\Map\Address;
use App\Form\Validator\ZipCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddressType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressType extends AbstractType
{
    private $translator;

    /**
     * AddressType constructor.
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
        $builder->add('search', Type\SearchType::class, [
            'label' => $this->translator->trans("Rechercher une adresse", [], 'admin'),
            'required' => false,
            'mapped' => false,
            'attr' => [
                'class' => 'input-places',
                'placeholder' => $this->translator->trans('Rechercher...', [], 'admin')
            ]
        ]);

        $builder->add('latitude', Type\TextType::class, [
            'label' => $this->translator->trans("Latitude", [], 'admin'),
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'latitude',
                'placeholder' => $this->translator->trans('Saisissez une latitude', [], 'admin')
            ]
        ]);

        $builder->add('longitude', Type\TextType::class, [
            'label' => $this->translator->trans("Longitude", [], 'admin'),
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'longitude',
                'placeholder' => $this->translator->trans('Saisissez une longitude', [], 'admin')
            ]
        ]);

        $builder->add('name', Type\TextType::class, [
            'label' => $this->translator->trans("Raison sociale", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-6',
                'placeholder' => $this->translator->trans('Saisissez une raison sociale', [], 'admin')
            ]
        ]);

        $builder->add('address', Type\TextType::class, [
            'label' => $this->translator->trans("Adresse", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-9',
                'class' => 'address',
                'placeholder' => $this->translator->trans('Saisissez une adresse', [], 'admin')
            ]
        ]);

        $builder->add('city', Type\TextType::class, [
            'label' => $this->translator->trans("Ville", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'city',
                'placeholder' => $this->translator->trans('Saisissez une ville', [], 'admin')
            ]
        ]);

        $builder->add('zipCode', Type\TextType::class, [
            'label' => $this->translator->trans("Code postal", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'zip-code',
                'placeholder' => $this->translator->trans('Saisissez un code postal', [], 'admin')
            ],
            'constraints' => [new ZipCode()]
        ]);

        $builder->add('department', Type\TextType::class, [
            'label' => $this->translator->trans("Département", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'department',
                'placeholder' => $this->translator->trans('Saisissez une département', [], 'admin')
            ]
        ]);

        $builder->add('region', Type\TextType::class, [
            'label' => $this->translator->trans("Région", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-3',
                'class' => 'region',
                'placeholder' => $this->translator->trans('Saisissez une région', [], 'admin')
            ]
        ]);

        $builder->add('country', Type\CountryType::class, [
            'label' => $this->translator->trans("Pays", [], 'admin'),
            'required' => false,
            'display' => 'basic',
            'placeholder' => $this->translator->trans('Sélectionnez un pays', [], 'admin'),
            'attr' => ['group' => 'col-md-3', 'class' => 'country']
        ]);

        $builder->add('googleMapUrl', Type\UrlType::class, [
            'label' => $this->translator->trans("Google map URL", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-6',
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin')
            ]
        ]);

        $builder->add('googleMapDirectionUrl', Type\UrlType::class, [
            'label' => $this->translator->trans("Google map itinéraire URL", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-6',
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin')
            ]
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
            'translation_domain' => 'admin'
        ]);
    }
}