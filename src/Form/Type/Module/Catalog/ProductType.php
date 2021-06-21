<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Module\Catalog\Category;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Catalog;
use App\Entity\Module\Catalog\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductType
 *
 * @property TranslatorInterface $translator
 * @property Request $request
 * @property bool $isLayoutUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProductType extends AbstractType
{
    private $translator;
    private $request;
    private $isLayoutUser;
    private $website;

    /**
     * ProductType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->request = $requestStack->getMasterRequest();
        $this->isLayoutUser = $authorizationChecker->isGranted('ROLE_LAYOUT_CATALOGPRODUCT');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var Product $data */
        $data = $builder->getData();
        $isNew = !$data->getId();
        $displayCatalog = !$this->request->get('catalog') && $isNew || !$isNew;
        $this->website = $options['website'];

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew && $displayCatalog ? 'col-md-9' : 'col-12',
            'class' => 'refer-code'
        ]);

        if ($displayCatalog) {

            $builder->add('catalog', EntityType::class, [
                'label' => $this->translator->trans('Catalogue', [], 'admin'),
                'display' => 'search',
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'group' => $isNew ? "col-md-3" : "col-md-4",
                ],
                'class' => Catalog::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('c.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'constraints' => [new Assert\NotBlank()]
            ]);

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans('Catégories', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'class' => Category::class,
                'attr' => [
                    'group' => $isNew ? "col-12" : "col-md-8",
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
        }

        if ($isNew && $this->isLayoutUser) {
            $builder->add('customLayout', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
                'attr' => ['group' => "col-12 text-center"]
            ]);
        }

        if (!$isNew) {

            $builder->add('promote', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Mettre en avant', [], 'admin'),
                'attr' => ['group' => 'col-md-2 d-flex align-items-end', 'class' => 'w-100']
            ]);

            $builder->add('catalogBeforePost', Type\HiddenType::class, [
                'mapped' => false,
                'data' => $data->getCatalog()->getId()
            ]);

            $urls = new WidgetType\UrlsCollectionType($this->translator);
            $urls->add($builder, ['display_seo' => true]);

            $dates = new WidgetType\PublicationDatesType($this->translator);
            $dates->add($builder, [
                'startGroup' => 'col-md-4',
                'endGroup' => 'col-md-4'
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'fields' => ['title', 'introduction', 'body'],
                'excludes_fields' => ['headerTable'],
                'disableTitle' => true,
            ]);

            if ($this->isLayoutUser) {
                $builder->add('customLayout', Type\CheckboxType::class, [
                    'required' => false,
                    'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
                    'attr' => ['group' => "col-md-4", 'data-config' => true]
                ]);
            }

            $builder->add('values', CollectionType::class, [
                'label' => false,
                'entry_type' => FeatureValueProductType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'block_name' => 'values',
                'entry_options' => [
                    'product' => $data,
                    'attr' => ['class' => 'value'],
                    'website' => $this->website
                ]
            ]);

            $builder->add('lots', CollectionType::class, [
                'label' => false,
                'entry_type' => LotType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'lots',
                        'disableTitle' => true,
                        'button' => $this->translator->trans('Ajouter un lot', [], 'admin')
                    ],
                    'website' => $this->website
                ]
            ]);

            $builder->add('products', EntityType::class, [
                'label' => $this->translator->trans("Sélectionnez", [], 'admin'),
                'required' => false,
                'class' => Product::class,
                'attr' => [
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('p.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'display' => "search"
            ]);

            $builder->add('videos', CollectionType::class, [
                'label' => false,
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'video',
                        'disableTitle' => true,
                        'caption' => $this->translator->trans('Vidéos', [], 'admin'),
                        'button' => $this->translator->trans('Ajouter une vidéo', [], 'admin')
                    ],
                    'website' => $this->website
                ]
            ]);

            $builder->add('informations', CollectionType::class, [
                'label' => false,
                'entry_type' => InformationType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'information',
                        'disableTitle' => true,
                        'button' => false
                    ],
                    'website' => $this->website
                ]
            ]);

        } else {

            $save = new WidgetType\SubmitType($this->translator);
            $save->add($builder);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}