<?php

namespace App\Form\Type\Information;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Information\Phone;
use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PhoneType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PhoneType extends AbstractType
{
    private $translator;

    /**
     * PhoneType constructor.
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
        /** @var Website $website */
        $website = $options['website'];
        $configuration = $website->getConfiguration();
        $locales = $this->getLocales($configuration);
        $multiLocales = count($locales) > 1;

        $builder->add('number', Type\TextType::class, [
            'label' => $this->translator->trans('Numéro de téléphone', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un numéro', [], 'admin')
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Validator\Phone()
            ]
        ]);

        $builder->add('tagNumber', Type\TextType::class, [
            'label' => $this->translator->trans('Numéro de téléphone (href)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un numéro', [], 'admin')
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Validator\Phone()
            ]
        ]);

        if($options['entitled']) {
            $builder->add('entitled', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Intitulé', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un intitulé', [], 'admin')
                ]
            ]);
        }

        if($options['type']) {

            $builder->add('type', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Type", [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('Fixe', [], 'admin') => 'fixe',
                    $this->translator->trans('Portable', [], 'admin') => 'mobile',
                    $this->translator->trans('Fax', [], 'admin') => 'fax'
                ],
                'attr' => [
                    'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'constraints' => [new Assert\NotBlank()]
            ]);
        }

        if ($multiLocales && $options['locale']) {

            $builder->add('locale', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Langue', [], 'admin'),
                'choices' => $locales,
                'choice_translation_domain' => false,
                'attr' => ['class' => 'select-icons'],
                'choice_attr' => function ($iso, $key, $value) {
                    return [
                        'data-image' => '/medias/icons/flags/' . strtolower($iso) . '.svg',
                        'data-class' => 'flag mt-min',
                        'data-text' => true,
                        'data-height' => 14,
                        'data-width' => 19,
                    ];
                },
                'constraints' => [new Assert\NotBlank()]
            ]);

        } else {

            $builder->add('locale', Type\HiddenType::class, [
                'data' => $configuration->getLocale()
            ]);
        }

        if($options['zones']) {

            $choices[$this->translator->trans('Page de contact', [], 'admin')] = 'contact';
            $choices[$this->translator->trans('Navigation', [], 'admin')] = 'header';
            $choices[$this->translator->trans('Pied de page', [], 'admin')] = 'footer';
            $choices[$this->translator->trans('E-mail', [], 'admin')] = 'email';
            if ($website->getSeoConfiguration()->getMicroData()) {
                $choices[$this->translator->trans('Micro data', [], 'admin')] = 'microdata';
            }

            $builder->add('zones', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Zones d'affichage", [], 'admin'),
                'display' => 'search',
                'multiple' => true,
                'required' => false,
                'choices' => $choices,
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez une zone', [], 'admin')
                ]
            ]);
        }
    }

    /**
     * Get Website locales
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getLocales(Configuration $configuration)
    {
        $defaultLocale = $configuration->getLocale();
        $locales[Languages::getName($defaultLocale)] = $defaultLocale;
        foreach ($configuration->getLocales() as $locale) {
            $locales[Languages::getName($locale)] = $locale;
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
            'website' => NULL,
            'locale' => true,
            'entitled' => true,
            'type' => true,
            'zones' => true,
            'prototypePosition' => true,
            'translation_domain' => 'admin'
        ]);
    }
}