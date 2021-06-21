<?php

namespace App\Form\Widget;

use App\Form\Validator\UniqDate;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PublicationDatesType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PublicationDatesType
{
    private $translator;

    /**
     * PublicationDatesType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add fields
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $requiredFields = !empty($options['required_fields']) ? $options['required_fields'] : [];
        $uniqFields = !empty($options['uniq_fields']) ? $options['uniq_fields'] : [];
        $entity = !empty($options['entity']) ? $options['entity'] : $builder->getData();
        $hasDatePicker = !empty($options['datePicker']) && $options['datePicker'];

        $publicationStart = $entity->getPublicationStart();
        if (empty($options['disabled_default'])) {
            $publicationStart = $entity && $entity->getPublicationStart() ? $entity->getPublicationStart() : new \DateTime('now');
        }

        $constraints = in_array('publicationStart', $requiredFields) ? [new Assert\NotBlank()] : [];
        if (in_array('publicationStart', $uniqFields)) {
            $constraints[] = new UniqDate();
        }
        $builder->add('publicationStart', DateTimeType::class, [
            'required' => in_array('publicationStart', $requiredFields),
            'label' => !empty($options['startLabel']) ? $options['startLabel'] : $this->translator->trans('Début de la publication', [], 'admin'),
            'placeholder' => $hasDatePicker ? $this->translator->trans('Sélectionnez une date', [], 'admin') : $this->placeholders(),
            'widget' => $hasDatePicker ? 'single_text' : NULL,
            'format' => $hasDatePicker ? 'dd/MM/YYYY HH:mm': DateTimeType::HTML5_FORMAT,
            'data' => $publicationStart,
            'attr' => [
                'group' => !empty($options['startGroup']) ? $options['startGroup'] . '  datetime-group' : 'col-md-4 datetime-group',
                'class' => $hasDatePicker ? 'datepicker' : NULL,
                'placeholder' => $hasDatePicker ? $this->translator->trans('Sélectionnez une date', [], 'admin') : NULL
            ],
            'constraints' => $constraints
        ]);

        $constraints = in_array('publicationEnd', $requiredFields) ? [new Assert\NotBlank()] : [];
        if (in_array('publicationEnd', $uniqFields)) {
            $constraints[] = new UniqDate();
        }
        $builder->add('publicationEnd', DateTimeType::class, [
            'required' => in_array('publicationEnd', $requiredFields),
            'label' => !empty($options['endLabel']) ? $options['endLabel'] : $this->translator->trans('Fin de la publication', [], 'admin'),
            'placeholder' => $hasDatePicker ? $this->translator->trans('Sélectionnez une date', [], 'admin') : $this->placeholders(),
            'widget' => $hasDatePicker ? 'single_text' : NULL,
            'format' => $hasDatePicker ? 'dd/MM/YYYY HH:mm': DateTimeType::HTML5_FORMAT,
            'attr' => [
                'group' => !empty($options['endGroup']) ? $options['endGroup'] . '  datetime-group' : 'col-md-4 datetime-group',
                'class' => $hasDatePicker ? 'datepicker' : NULL,
                'placeholder' => $hasDatePicker ? $this->translator->trans('Sélectionnez une date', [], 'admin') : NULL
            ],
            'constraints' => $constraints
        ]);
    }

    /**
     * Get placeholders
     *
     * @return array
     */
    private function placeholders(): array
    {
        return [
            'year' => $this->translator->trans('Année', [], 'admin'),
            'month' => $this->translator->trans('Mois', [], 'admin'),
            'day' => $this->translator->trans('Jour', [], 'admin'),
            'hour' => $this->translator->trans('Heure', [], 'admin'),
            'minute' => $this->translator->trans('Minute', [], 'admin'),
            'second' => $this->translator->trans('Seconde', [], 'admin'),
        ];
    }
}