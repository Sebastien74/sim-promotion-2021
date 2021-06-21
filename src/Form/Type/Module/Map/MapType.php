<?php

namespace App\Form\Type\Module\Map;

use App\Entity\Module\Map\Map;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MapType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MapType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * FormType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew) {

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
                'label' => $this->translator->trans("Latitude de centrage", [], 'admin'),
                'attr' => [
                    'group' => 'col-md-3',
                    'class' => 'latitude',
                    'placeholder' => $this->translator->trans('Saisissez une latitude', [], 'admin')
                ],
                'constraints' => [new Assert\NotBlank()]
            ]);

            $builder->add('longitude', Type\TextType::class, [
                'label' => $this->translator->trans("Longitude de centrage", [], 'admin'),
                'attr' => [
                    'group' => 'col-md-3',
                    'class' => 'longitude',
                    'placeholder' => $this->translator->trans('Saisissez une longitude', [], 'admin')
                ],
                'constraints' => [new Assert\NotBlank()]
            ]);

            if ($this->isInternalUser) {

                $builder->add('zoom', Type\IntegerType::class, [
                    'label' => $this->translator->trans("Zoom", [], 'admin'),
                    'attr' => ['group' => 'col-md-2', 'data-config' => true, 'min' => 1, 'max' => 16]
                ]);

                $builder->add('minZoom', Type\IntegerType::class, [
                    'label' => $this->translator->trans("Zoom minimum", [], 'admin'),
                    'attr' => ['group' => 'col-md-2', 'data-config' => true, 'min' => 1, 'max' => 16]
                ]);

                $builder->add('maxZoom', Type\IntegerType::class, [
                    'label' => $this->translator->trans("Zoom maximum", [], 'admin'),
                    'attr' => ['group' => 'col-md-2', 'data-config' => true, 'min' => 1, 'max' => 25]
                ]);

                $builder->add('layer', Type\UrlType::class, [
                    'required' => false,
                    'label' => $this->translator->trans("Template de la carte", [], 'admin'),
                    'attr' => [
                        'group' => 'col-md-6',
                        'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                        'data-config' => true
                    ],
                    'help' => '<a href="https://leaflet-extras.github.io/leaflet-providers/preview/" target="_blank">' . $this->translator->trans("Trouver un templates", [], 'admin') . '</a>'
                ]);

                $builder->add('displayFilters', Type\CheckboxType::class, [
                    'required' => false,
                    'label' => $this->translator->trans('Afficher les filtres ?', [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'data-config' => true]
                ]);

                $builder->add('multiFilters', Type\CheckboxType::class, [
                    'required' => false,
                    'label' => $this->translator->trans('Choix multiple des filtres ?', [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'data-config' => true]
                ]);

                $builder->add('markerClusters', Type\CheckboxType::class, [
                    'required' => false,
                    'label' => $this->translator->trans('Activer les groupes de points ?', [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'data-config' => true]
                ]);
            }

            $builder->add('isDefault', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Carte principale ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2 my-md-auto']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Generate choices iterations
     *
     * @param int $iteration
     * @return array
     */
    private function iterations(int $iteration)
    {
        $iterations = [];

        for ($x = 1; $x <= $iteration; $x++) {
            $iterations[$x] = $x;
        }

        return $iterations;
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Map::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}