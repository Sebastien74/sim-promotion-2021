<?php

namespace App\Form\Type\Core\Configuration;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Form\Type\Layout\Management\CssClassType;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationType extends AbstractType
{
    private $translator;
    private $kernel;
    private $website;

    /**
     * ConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->website = $options['website'];

        $mediaRelation = new WidgetType\MediaRelationsCollectionType($this->translator);
        $mediaRelation->add($builder, ['entry_options' => ['onlyMedia' => true, 'active' => true]]);

        $builder->add('colors', CollectionType::class, [
            'label' => false,
            'entry_type' => ColorType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => ['class' => 'color'],
                'website' => $options['website']
            ]
        ]);

        $builder->add('transitions', CollectionType::class, [
            'label' => false,
            'entry_type' => TransitionType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => [
                    'class' => 'transition',
                    'icon' => 'fal fa-hurricane',
                    'group' => 'col-md-4',
                    'caption' => $this->translator->trans('Transitions', [], 'admin'),
                    'button' => $this->translator->trans('Ajouter une transition', [], 'admin')
                ],
                'website' => $options['website']
            ]
        ]);

        $builder->add('cssClasses', CollectionType::class, [
            'label' => false,
            'entry_type' => CssClassType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => ['class' => 'cssclass'],
                'website' => $options['website']
            ]
        ]);

        $choices = [];
        for ($x = 1; $x <= 10; $x++) {
            $choices['Box' . $x] = 'box' . $x;
        }
        $choices[$this->translator->trans('Personnalisé', [], 'admin')] = 'custom';

        $builder->add('hoverTheme', ChoiceType::class, [
            'label' => $this->translator->trans('Hover médias thème', [], 'admin'),
            'required' => false,
            'expanded' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => ['group' => 'col-md-2'],
            'choices' => $choices
        ]);

        $builder->add('frontFonts', WidgetType\FontsType::class, [
            'label' => $this->translator->trans('Polices site', [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => 'col-md-5'
            ]
        ]);

        $builder->add('adminFonts', WidgetType\FontsType::class, [
            'label' => $this->translator->trans('Polices éditeur administration', [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => 'col-md-5'
            ],
        ]);

        $builder->add('pages', EntityType::class, [
            'required' => false,
            'label' => $this->translator->trans('Pages principales', [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            ],
            'class' => Page::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('p')
                    ->leftJoin('p.urls', 'u')
                    ->andWhere('p.website = :website')
                    ->andWhere('p.deletable = :deletable')
                    ->andWhere('u.isOnline = :isOnline')
                    ->setParameter('website', $this->website)
                    ->setParameter('deletable', true)
                    ->setParameter('isOnline', true)
                    ->addSelect('u')
                    ->orderBy('p.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'display' => 'search',
            'multiple' => true
        ]);

        $builder->add('website', WebsiteType::class, [
            'label' => false
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_save' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}