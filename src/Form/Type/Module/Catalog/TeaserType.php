<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Module\Catalog\Category;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Teaser;
use Doctrine\ORM\EntityRepository;
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
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TeaserType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $website;

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
        $this->website = $options['website'];

        $adminNameGroup = 'col-12';
        if (!$isNew && $this->isInternalUser) {
            $adminNameGroup = 'col-md-9';
        }

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $adminNameGroup,
            'slugGroup' => 'col-md-3',
            'slug-internal' => $this->isInternalUser
        ]);

        if (!$isNew) {

            if ($this->isInternalUser) {

                $builder->add('nbrItems', Type\IntegerType::class, [
                    'label' => $this->translator->trans("Nombre de produits par teaser", [], 'admin'),
                    'attr' => [
                        'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                        'group' => 'col-md-4',
                        'data-config' => true
                    ]
                ]);

                $builder->add('itemsPerSlide', Type\IntegerType::class, [
                    'required' => false,
                    'label' => $this->translator->trans("Nombre de produits par slide", [], 'admin'),
                    'attr' => [
                        'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                        'group' => 'col-md-4',
                        'data-config' => true
                    ],
                ]);

                $builder->add('orderBy', Type\ChoiceType::class, [
                    'label' => $this->translator->trans('Ordonner les produits par', [], 'admin'),
                    'display' => 'search',
                    'attr' => ['group' => 'col-md-4', 'data-config' => true],
                    'choices' => [
                        $this->translator->trans('Dates (croissantes)', [], 'admin') => 'publicationStart-asc',
                        $this->translator->trans('Dates (décroissantes)', [], 'admin') => 'publicationStart-desc',
                        $this->translator->trans('Catégories (croissantes)', [], 'admin') => 'category-asc',
                        $this->translator->trans('Catégories (décroissantes)', [], 'admin') => 'category-desc',
                        $this->translator->trans('Positions (croissantes)', [], 'admin') => 'position-asc',
                        $this->translator->trans('Positions (décroissantes)', [], 'admin') => 'position-desc',
                        $this->translator->trans('Aléatoire', [], 'admin') => 'random',
                    ]
                ]);

                $builder->add('promote', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Afficher uniquement les produits mis en avant', [], 'admin'),
                    'attr' => ['group' => 'col-md-4', 'class' => 'w-100', 'data-config' => true]
                ]);
            }

            $builder->add('catalogs', EntityType::class, [
                'label' => $this->translator->trans('Catalogues', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'class' => Catalog::class,
                'attr' => [
                    'group' => 'col-md-6',
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true
            ]);

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans('Catégories', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'class' => Category::class,
                'attr' => [
                    'group' => 'col-md-6',
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
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