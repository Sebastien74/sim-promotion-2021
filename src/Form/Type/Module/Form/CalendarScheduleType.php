<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\CalendarSchedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarScheduleType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarScheduleType extends AbstractType
{
    private $translator;

    /**
     * CalendarScheduleType constructor.
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
        $builder->add('timeRanges', Type\CollectionType::class, [
            'label' => false,
            'entry_type' => CalendarTimeRangeType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => ['attr' => ['icon' => 'fal fa-clock', 'group' => 'col-md-3']]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarSchedule::class,
            'website' => NULL,
            'legend' => $this->translator->trans('Jours de la semaine', [], 'admin'),
            'translation_domain' => 'admin'
        ]);
    }
}