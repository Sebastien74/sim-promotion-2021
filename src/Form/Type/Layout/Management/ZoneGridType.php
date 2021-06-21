<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Grid;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ZoneGridType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneGridType extends AbstractType
{
    private $translator;

    /**
     * ZoneGridType constructor.
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
        $builder->add('grid', EntityType::class, [
            'label' => false,
            'class' => Grid::class,
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'expanded' => true
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Ajouter', [], 'admin'),
            'attr' => [
                'class' => 'btn-info d-none edit-element-submit-btn btn-lg disable-preloader'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}