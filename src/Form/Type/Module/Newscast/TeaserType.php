<?php

namespace App\Form\Type\Module\Newscast;

use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Teaser;
use App\Form\Widget as WidgetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TeaserType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TeaserType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * TeaserType constructor.
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

        $adminNameGroup = 'col-12';
        if (!$isNew && $this->isInternalUser) {
            $adminNameGroup = 'col-md-4';
        } elseif (!$isNew) {
            $adminNameGroup = 'col-md-6';
        }

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $adminNameGroup,
            'slugGroup' => 'col-sm-2',
            'slug-internal' => $this->isInternalUser
        ]);

        if (!$isNew) {

            if ($this->isInternalUser) {

                $builder->add('nbrItems', Type\IntegerType::class, [
                    'label' => $this->translator->trans("Nombre d'actualités par teaser", [], 'admin'),
                    'attr' => [
                        'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                        'group' => 'col-md-4',
                        'data-config' => true
                    ]
                ]);

                $builder->add('itemsPerSlide', Type\IntegerType::class, [
                    'required' => false,
                    'label' => $this->translator->trans("Nombre d'actualités par slide", [], 'admin'),
                    'attr' => [
                        'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                        'group' => 'col-md-4',
                        'data-config' => true
                    ],
                ]);

                $builder->add('orderBy', Type\ChoiceType::class, [
                    'label' => $this->translator->trans('Ordonner les actualités par', [], 'admin'),
                    'display' => 'search',
                    'attr' => ['group' => 'col-md-4', 'data-config' => true],
                    'choices' => [
                        $this->translator->trans('Dates (croissantes)', [], 'admin') => 'publicationStart-asc',
                        $this->translator->trans('Dates (décroissantes)', [], 'admin') => 'publicationStart-desc',
                        $this->translator->trans('Positions (croissantes)', [], 'admin') => 'position-asc',
                        $this->translator->trans('Positions (décroissantes)', [], 'admin') => 'position-desc'
                    ]
                ]);

                $builder->add('hasSlider', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Afficher un slider', [], 'admin'),
                    'attr' => ['group' => 'col-md-4', 'class' => 'w-100', 'data-config' => true]
                ]);

                $builder->add('promote', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Afficher uniquement les actualités mises en avant", [], 'admin'),
                    'attr' => ['group' => 'col-md-4', 'class' => 'w-100', 'data-config' => true]
                ]);
            }

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans('Catégories', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'class' => Category::class,
                'attr' => ['group' => !$isNew ? 'col-md-6' : 'col-12'],
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'fields' => ['title']
            ]);
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
            'data_class' => Teaser::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}