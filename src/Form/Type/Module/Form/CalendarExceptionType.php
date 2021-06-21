<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\CalendarException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarExceptionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarExceptionType extends AbstractType
{
    private $translator;

    /**
     * CalendarExceptionType constructor.
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
        $builder->add('startDate', Type\DateTimeType::class, [
            'required' => true,
            'label' => $this->translator->trans('Date de début', [], 'admin'),
            'placeholder' => [
                'year' => $this->translator->trans('Année', [], 'admin'),
                'month' => $this->translator->trans('Mois', [], 'admin'),
                'day' => $this->translator->trans('Jour', [], 'admin'),
                'hour' => $this->translator->trans('Heure', [], 'admin'),
                'minute' => $this->translator->trans('Minute', [], 'admin'),
                'second' => $this->translator->trans('Seconde', [], 'admin'),
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('endDate', Type\DateTimeType::class, [
            'required' => true,
            'placeholder' => [
                'year' => $this->translator->trans('Année', [], 'admin'),
                'month' => $this->translator->trans('Mois', [], 'admin'),
                'day' => $this->translator->trans('Jour', [], 'admin'),
                'hour' => $this->translator->trans('Heure', [], 'admin'),
                'minute' => $this->translator->trans('Minute', [], 'admin'),
                'second' => $this->translator->trans('Seconde', [], 'admin'),
            ],
            'label' => $this->translator->trans('Date de fin', [], 'admin'),
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarException::class,
            'website' => NULL,
            'legend' => $this->translator->trans('Exceptions', [], 'admin'),
            'translation_domain' => 'admin'
        ]);
    }
}