<?php

namespace App\Form\Type\Module\Slider;

use App\Entity\Module\Slider\Slider;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SliderType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property bool $allowedBanner
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SliderType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $allowedBanner;

    /**
     * SliderType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->allowedBanner = $authorizationChecker->isGranted('ROLE_BANNER');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'slug-internal' => $this->isInternalUser,
            'adminNameGroup' => 'col-sm-9'
        ]);

        if (!$isNew && $this->isInternalUser) {

            $builder->add('template', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'admin'),
                'display' => 'search',
                'attr' => [
                    'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ],
                'choices' => [
                    'Bootstrap' => "bootstrap",
                    'Splide' => "splide"
                ]
            ]);

            $builder->add('intervalDuration', Type\IntegerType::class, [
                'label' => $this->translator->trans('Intervalle', [], 'admin'),
                'help' => $this->translator->trans('Milliseconds', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ]
            ]);

            $builder->add('effect', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Effet', [], 'admin'),
                'display' => 'search',
                'attr' => [
                    'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ],
                'choices' => [
                    'Fade' => "fade",
                    'Slide' => "slide"
                ]
            ]);

            $builder->add('itemsPerSlide', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre d'images par slide (Ordinateur)", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ]
            ]);

            $builder->add('itemsPerSlideMiniPC', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre d'images par slide (Mini PC)", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ]
            ]);

            $builder->add('itemsPerSlideTablet', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre d'images par slide (Tablette)", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ]
            ]);

            $builder->add('itemsPerSlideMobile', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre d'images par slide (Mobile)", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-3',
                    'data-config' => true
                ]
            ]);

            $builder->add('indicators', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Afficher les points de navigation", [], 'admin'),
                'attr' => ['group' => 'col-md-3 d-flex align-items-end', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('control', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Afficher les flèches de navigation", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('autoplay', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Lecture automatique", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('pause', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Pause au survol", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('popup', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Afficher popup au clic des images", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            if ($this->allowedBanner) {
                $builder->add('banner', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Afficher sous forme de bannière", [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
                ]);
            }
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Slider::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}