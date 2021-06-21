<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\CalendarTimeRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarTimeRangeType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarTimeRangeType extends AbstractType
{
    private $translator;

    /**
     * CalendarTimeRangeType constructor.
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
        $builder->add('startHour', Type\TimeType::class, [
            'label' => $this->translator->trans('Ouverture', [], 'admin'),
            'attr' => ['group' => 'hours-field-group col-md-6'],
            'placeholder' => [
                'hour' => $this->translator->trans('Heure', [], 'admin'),
                'minute' => $this->translator->trans('Minute', [], 'admin'),
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('endHour', Type\TimeType::class, [
            'label' => $this->translator->trans('Fermeture', [], 'admin'),
            'attr' => ['group' => 'hours-field-group col-md-6'],
            'placeholder' => [
                'hour' => $this->translator->trans('Heure', [], 'admin'),
                'minute' => $this->translator->trans('Minute', [], 'admin'),
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarTimeRange::class,
            'website' => NULL,
            'legend' => $this->translator->trans('Plages horaires', [], 'admin'),
            'legend_property' => 'adminName',
            'translation_domain' => 'admin'
        ]);
    }
}